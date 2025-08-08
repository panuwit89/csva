<?php

namespace App\Jobs;

use App\Models\Conversation; // 👈 import
use App\Repositories\ConversationRepository; // 👈 import
use App\Services\FastAPIService; // 👈 import
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log; // 👈 import
use App\Models\MessageAttachment;
use Illuminate\Support\Facades\Storage;

class ProcessPromptWithFastAPI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3; // ให้ลองทำงาน 3 ครั้งถ้าล้มเหลว

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $conversationId,
        public string $prompt,
        public array $attachmentIds = [] // 👈 รับเป็น array
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FastAPIService $fastAPIService, ConversationRepository $conversationRepository): void
    {
        try {
            $response = '';

            if (!empty($this->attachmentIds)) {
                $fileData = [];
                $attachments = MessageAttachment::findMany($this->attachmentIds);
                foreach ($attachments as $attachment) {
                    $fullPath = Storage::disk('public')->path($attachment->path);
                    if (file_exists($fullPath)) {
                        $fileData[] = ['path' => $fullPath, 'original_name' => $attachment->original_name];
                    }
                }
                $response = $fastAPIService->sendFilesAndPrompt($fileData, $this->prompt, $this->conversationId);
            } else {
                $history = $conversationRepository->getConversationMessageWithAttachments($this->conversationId);
                $response = $fastAPIService->sendPrompt($this->prompt, $this->conversationId, $history->toArray());
            }

            // --- START: ส่วนที่แก้ไข ---

            // 1. ค้นหา Conversation ด้วย Eloquent โดยตรง
            $conversation = Conversation::find($this->conversationId);

            // 2. ตรวจสอบว่าเจอ Conversation จริงๆ ก่อนทำงานต่อ
            if (!$conversation) {
                Log::error("Job failed: Conversation with ID {$this->conversationId} not found.");
                return; // ออกจาก Job ไปเลยถ้าไม่เจอ
            }

            // 3. บันทึกคำตอบของ AI ลงในฐานข้อมูล (ย้ายมาทำก่อน)
            if ($response) {
                $conversation->messages()->create([
                    'role' => 'model',
                    'content' => $response,
                ]);
            }

            // 4. อัปเดต title ถ้าจำเป็น (ใช้ object ที่หามาแล้ว)
            if ($conversation->title == "New Conversation") {
                try {
                    $chatName = $fastAPIService->defineChatName($this->conversationId);
                    $conversation->title = $chatName;
                } catch (\Exception $e) {
                    Log::error('Failed to define chat name in job: ' . $e->getMessage());
                }
            }

            // 5. อัปเดต timestamp และบันทึกการเปลี่ยนแปลงทั้งหมดในครั้งเดียว
            $conversation->touch(); // touch() เป็นวิธีมาตรฐานในการอัปเดต updated_at
            // หรือจะใช้ $conversation->save(); ก็ได้ถ้ามีการแก้ title

            // --- END: ส่วนที่แก้ไข ---

        } catch (\Exception $e) {
            Log::error("Job ProcessPromptWithFastAPI failed for conv_id {$this->conversationId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // เพิ่ม trace เพื่อให้ debug ง่ายขึ้น
            ]);

            Conversation::find($this->conversationId)?->messages()->create([
                'role' => 'model',
                'content' => 'Sorry, there was an error processing the request in the background.',
            ]);

            throw $e;
        }
    }
}
