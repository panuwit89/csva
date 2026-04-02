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

    public int $timeout = 300;
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $conversationId,
        public string $prompt,
        public array  $attachmentIds = [],
        public array $userDocumentPaths = [],
        public array $userTags = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FastAPIService $fastAPIService, ConversationRepository $conversationRepository): void
    {
        try {
            $allFilesData = [];

            if (!empty($this->attachmentIds)) {
                $attachments = MessageAttachment::findMany($this->attachmentIds);
                foreach ($attachments as $attachment) {
                    $fullPath = Storage::disk('public')->path($attachment->path);
                    if (file_exists($fullPath)) {
                        $allFilesData[] = ['path' => $fullPath, 'original_name' => $attachment->original_name];
                    }
                }
            }

            $graduationKeywords = ['จบการศึกษา', 'สำเร็จการศึกษา', 'เช็คจบ', 'สถานะจบการศึกษา'];
            $isGraduationCheckIntent = false;
            foreach ($graduationKeywords as $keyword) {
                if (mb_stripos($this->prompt, $keyword) !== false) {
                    $isGraduationCheckIntent = true;
                    break;
                }
            }

            if ($isGraduationCheckIntent && !empty($this->userDocumentPaths)) {
                Log::info("Graduation check intent detected. Attaching user profile documents.", ['conv_id' => $this->conversationId]);
                foreach ($this->userDocumentPaths as $docInfo) {
                    $fullPath = Storage::disk('public')->path($docInfo['path']);
                    if (file_exists($fullPath)) {
                        $allFilesData[] = [
                            'path' => $fullPath,
                            'original_name' => $docInfo['original_name']
                        ];
                    } else {
                        Log::warning("User profile document not found at path: " . $docInfo['path']);
                    }
                }
            } else {
                Log::info("No graduation check intent detected or no profile docs. Skipping user profile documents.", ['conv_id' => $this->conversationId]);
            }
            Log::info("Total files prepared for FastAPI: " . count($allFilesData), ['conv_id' => $this->conversationId]);

            $responseContent = '';

            if (!empty($allFilesData)) {
                // กรณีมีไฟล์: ส่งทั้งไฟล์และข้อความ
                Log::info("Routing to general file processing for conv {$this->conversationId}");
                $responseContent = $fastAPIService->sendFilesAndPrompt($allFilesData, $this->prompt, $this->conversationId, $this->userTags);
            } else {
                // กรณีไม่มีไฟล์: ส่งเฉพาะข้อความ
                Log::info("Routing to text-only prompt for conv {$this->conversationId}");
                $history = $conversationRepository->getConversationMessageWithAttachments($this->conversationId);
                $responseContent = $fastAPIService->sendPrompt($this->prompt, $this->conversationId, $history->toArray(), $this->userTags);
            }

            // ค้นหา Conversation ด้วย Eloquent โดยตรง
            $conversation = Conversation::find($this->conversationId);

            // ตรวจสอบว่าเจอ Conversation จริงๆ ก่อนทำงานต่อ
            if (!$conversation) {
                Log::error("Job failed: Conversation with ID {$this->conversationId} not found.");
                return;
            }

            // ตรวจสอบว่าได้ข้อความกลับมาจริงๆ (ไม่เป็นค่าว่างหรือ null)
            if (!empty($responseContent)) {
                // บันทึกข้อความลง DB โดยตรง
                $newMessage = $conversation->messages()->create([
                    'role' => 'model',
                    'content' => $responseContent,
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
