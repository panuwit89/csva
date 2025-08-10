<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Repositories\ConversationRepository;
use App\Services\FastAPIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\MessageAttachment;
use Illuminate\Support\Facades\Storage;

class ProcessPromptWithFastAPI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $conversationId,
        public string $prompt,
        public array  $attachmentIds = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FastAPIService $fastAPIService, ConversationRepository $conversationRepository): void
    {
        try {
            $messageContent = ''; // ประกาศตัวแปรสำหรับเก็บข้อความสุดท้าย

            if (!empty($this->attachmentIds)) {
                $fileData = [];
                $attachments = MessageAttachment::findMany($this->attachmentIds);
                foreach ($attachments as $attachment) {
                    $fullPath = Storage::disk('public')->path($attachment->path);
                    if (file_exists($fullPath)) {
                        $fileData[] = ['path' => $fullPath, 'original_name' => $attachment->original_name];
                    }
                }
                $messageContent = $fastAPIService->sendFilesAndPrompt($fileData, $this->prompt, $this->conversationId);
            } else {
                $history = $conversationRepository->getConversationMessageWithAttachments($this->conversationId);
                $messageContent = $fastAPIService->sendPrompt($this->prompt, $this->conversationId, $history->toArray());
            }

            // ค้นหา Conversation ด้วย Eloquent โดยตรง
            $conversation = Conversation::find($this->conversationId);

            // ตรวจสอบว่าเจอ Conversation จริงๆ ก่อนทำงานต่อ
            if (!$conversation) {
                Log::error("Job failed: Conversation with ID {$this->conversationId} not found.");
                return;
            }

            // ตรวจสอบว่าได้ข้อความกลับมาจริงๆ (ไม่เป็นค่าว่างหรือ null)
            if (!empty($messageContent)) {
                // บันทึกข้อความลง DB โดยตรง
                $newMessage = $conversation->messages()->create([
                    'role' => 'model',
                    'content' => $messageContent,
                ]);
                Log::info("New message saved successfully with ID: " . $newMessage->id);
            } else {
                Log::error("Received an empty response from FastAPI.");
                $conversation->messages()->create([
                    'role' => 'model',
                    'content' => 'Sorry, I did not receive a valid response.',
                ]);
            }

            if ($conversation->title == "New Conversation") {
                try {
                    $chatName = $fastAPIService->defineChatName($this->conversationId);
                    $conversation->title = $chatName;
                } catch (\Exception $e) {
                    Log::error('Failed to define chat name in job: ' . $e->getMessage());
                }
            }

            $conversation->touch();

        } catch (\Exception $e) {
            Log::error("Job ProcessPromptWithFastAPI failed", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
