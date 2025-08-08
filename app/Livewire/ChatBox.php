<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Repositories\ConversationRepository;
use App\Services\FastAPIService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use App\Jobs\ProcessPromptWithFastAPI;

class ChatBox extends Component
{
    use WithFileUploads;

    public Conversation $conversation;
    public string $message = '';
    public $files = [];
    public $newFiles = [];
    public bool $loading = false;
    public bool $showFileUpload = false;
    public bool $showSuggestedPrompts = false;
    public bool $isWaitingForResponse = false;

    protected $rules = [
        'message' => 'required|string',
        'files.*' => 'nullable|file|max:10240', // 10MB max file size
        'newFiles.*' => 'nullable|file|max:10240', // 10MB max file size
    ];

    protected $messages = [
        'files.max' => 'สามารถเลือกไฟล์ได้ไม่เกิน 3 ไฟล์',
        'newFiles.max' => 'สามารถเลือกไฟล์ได้ไม่เกิน 3 ไฟล์',
        'files.*.max' => 'ขนาดไฟล์ต้องไม่เกิน 10MB',
        'newFiles.*.max' => 'ขนาดไฟล์ต้องไม่เกิน 10MB',
    ];

    public function getSuggestedPromptsProperty()
    {
        return [
            [
                'icon' => '🎓',
                'title' => 'เอกสารสำหรับยื่นจบการศึกษา',
                'prompts' => [
                    'ยื่นจบการศึกษาต้องใช้อะไรบ้าง',
                    'การยื่นจบการศึกษาต้องทำอย่างไรบ้าง'
                ]
            ],
            [
                'icon' => '🏢',
                'title' => 'สหกิจศึกษา',
                'prompts' => [
                    'ขอเอกสารเพื่อทำสหกิจศึกษาทำได้อย่างไร ต้องใช้อะไรบ้าง',
                    'แบบฟอร์มทำหนังสือส่งตัวเพื่อทำสหกิจศึกษา กรอกได้ที่ไหน',
                    'รับหนังสือส่งตัวเพื่อทำสหกิจศึกษาตัวจริงได้ที่ไหน'
                ]
            ],
            [
                'icon' => '📅',
                'title' => 'ปฏิทินการศึกษา',
                'prompts' => [
                    'ช่วงเวลาที่เปิดให้ชำระค่าธรรมเนียมการศึกษา',
                    'เกษตรแฟร์มีวันไหนบ้าง',
                    'เปิดภาคการศึกษาวันไหน',
                    'ลงทะเบียนเรียนวันไหนได้บ้าง'
                ]
            ],
            [
                'icon' => '📄',
                'title' => 'แบบฟอร์มเอกสารทางการศึกษา',
                'prompts' => [
                    'ต้องการเอกสารสำหรับลาป่วย',
                    'ต้องการแบบตรวจสอบหลักสูตร',
                    'เอกสารคำร้องขอเรียนร่วมดาวน์โหลดได้ที่ไหน'
                ]
            ],
            [
                'icon' => '🔧',
                'title' => 'แบบฟอร์มยืมอุปกรณ์ IOT',
                'prompts' => [
                    'การยืมอุปกรณ์ IOT ต้องทำอย่างไรบ้าง',
                    'ยืมอุปกรณ์ IOT ต้องใช้อะไรบ้าง',
                    'กรอกแบบฟอร์มยืมอุปกรณ์ IOT ได้ที่ไหน'
                ]
            ],
            [
                'icon' => '💰',
                'title' => 'เงื่อนไขการให้ทุนการศึกษา',
                'prompts' => [
                    'เงื่อนไขทุน 3A มีอะไรบ้าง',
                    'นิสิตปี 1 มีโอกาสได้ทุนอะไรบ้าง',
                    'แต่ละทุนการศึกษาได้กี่บาทบ้าง',
                    'จาก Transcript ฉันได้ทุนไหนบ้างในภาคเรียนล่าสุด'
                ]
            ],
            [
                'icon' => '💻',
                'title' => 'โครงงานวิทย์คอม',
                'prompts' => [
                    'ขั้นตอนการส่งโครงงานวิทยาการคอมพิวเตอร์',
                    'การส่งโครงงานวิทยาการคอมพิวเตอร์ ต้องส่งอะไรบ้าง',
                    'ส่งโครงงานวิทยาการคอมพิวเตอร์ที่ไหน'
                ]
            ],
            [
                'icon' => '🌐',
                'title' => 'ขอเอกสารในช่องทางออนไลน์',
                'prompts' => [
                    'ขอใบรายงานคะแนน (Transcript) ได้ที่ไหน',
                    'ขอใบรับรองฐานะการศึกษาได้ที่ไหน',
                    'ขอ Transcript ต้องใช้อะไรบ้าง'
                ]
            ],
            [
                'icon' => '❓',
                'title' => 'อื่นๆ',
                'prompts' => [
                    'ฉันผ่านเงื่อนไขการจบการศึกษาหรือยัง',
                    'แนะนำวิชาเลือกให้หน่อย'
                ]
            ]
        ];
    }

    public function mount(Conversation $conversation)
    {
        $this->conversation = $conversation;
        // Show suggested prompts when new conversation
        $this->showSuggestedPrompts = $this->conversation->messages()->count() === 0;
    }

    public function toggleFileUpload()
    {
        $this->showFileUpload = !$this->showFileUpload;
    }

    public function toggleSuggestedPrompts()
    {
        $this->showSuggestedPrompts = !$this->showSuggestedPrompts;
    }

    public function updatedNewFiles()
    {
        if ($this->newFiles) {
            foreach ($this->newFiles as $newFile) {
                // Check less than 3 files
                if (count($this->files) >= 3) {
                    $this->addError('files', 'สามารถเลือกไฟล์ได้ไม่เกิน 3 ไฟล์');
                    break;
                }

                $this->files[] = $newFile;
            }

            // Clear newFiles after add into files
            $this->newFiles = [];
        }
    }

    // Remove file at the specified index
    public function removeFile($index)
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files); // Re-index array

        // Clear error
        $this->resetErrorBag('files');
    }

    // Remove all files
    public function clearAllFiles()
    {
        $this->files = [];
        $this->newFiles = [];
        $this->resetErrorBag('files');
    }

    public function sendMessage($prompt = null)
    {
        if ($prompt) {
            $this->message = $prompt;
        }

        $this->validate();

        $this->loading = true;
        $this->showSuggestedPrompts = false; // Hide suggested prompts after sending message

//        try {
//            $fastAPIService = app(FastAPIService::class);
//            $conversationRepository = app(ConversationRepository::class);
//
//            $history_messages = $conversationRepository->getConversationMessageWithAttachments($this->conversation->id);
//
//            $uploadedFiles = [];
//
//            if (count($this->files) > 0) {
//                foreach ($this->files as $file) {
//                    // Create proper UploadedFile instances from Livewire temporary files
//                    $tempPath = $file->getRealPath();
//
//                    $uploadedFile = new UploadedFile(
//                        $tempPath,
//                        $file->getClientOriginalName(),
//                        $file->getMimeType(),
//                        null,
//                        true
//                    );
//
//                    $uploadedFiles[] = $uploadedFile;
//                }
//
//                // Get response from Fast API with files
//                $response = $fastAPIService->sendFilesAndPrompt($uploadedFiles, $this->message, $this->conversation->id);
//            } else {
//                // No files, just send text prompt
//                $response = $fastAPIService->sendPrompt($this->message, $this->conversation->id, $history_messages->toArray());
//            }
//
//            // Store user message
//            $userMessage = $this->conversation->messages()->create([
//                'role' => 'user',
//                'content' => $this->message,
//            ]);
//
//            // Store file attachments if any
//            if (count($this->files) > 0) {
//                foreach ($this->files as $file) {
//                    // Save files to permanent storage
//                    $path = $file->store('chat_attachments/' . $this->conversation->id, 'public');
//
//                    // Create attachment record
//                    $userMessage->attachments()->create([
//                        'path' => $path,
//                        'original_name' => $file->getClientOriginalName(),
//                        'mime_type' => $file->getMimeType(),
//                        'size' => $file->getSize(),
//                    ]);
//                }
//            }
//
//            // Store model response
//            $this->conversation->messages()->create([
//                'role' => 'model',
//                'content' => $response,
//            ]);
//
//            $conversationRepository->update(['updated_at' => now()], $this->conversation->id);
//
//            // Call define chat name directly
//            try {
//                // If not defined
//                if ($this->conversation->title == "New Conversation") {
//                    $chatName = $fastAPIService->defineChatName($this->conversation->id);
//                    $conversationRepository->update(['title' => $chatName], $this->conversation->id);
//                    Log::info('Successfully defined chat name: ' . $chatName);
//                }
//            } catch (\Exception $e) {
//                Log::error('Failed to define chat name: ' . $e->getMessage());
//            }
//
//            // Reset the properties
//            $this->message = '';
//            $this->files = [];
//            $this->newFiles = [];
//            $this->showFileUpload = false;
//            $this->resetErrorBag();
//
//            // Clear the input field via JavaScript
//            $this->js("document.getElementById('sendMessageComplete').value = ''");

        try {
            // --- ส่วนที่แก้ไข ---

            // 1. บันทึกข้อความและไฟล์ของผู้ใช้ลง DB ทันที เพื่อให้ User เห็นข้อความตัวเองเลย
            $userMessage = $this->conversation->messages()->create([
                'role' => 'user',
                'content' => $this->message,
            ]);

            $attachmentIds = []; // 👈 สร้าง array เพื่อเก็บ ID ของไฟล์แนบ
            if (count($this->files) > 0) {
                foreach ($this->files as $file) {
                    // 2. บันทึกไฟล์ลง storage ถาวร (เช่น public)
                    $path = $file->store('chat_attachments/' . $this->conversation->id, 'public');

                    // 3. สร้าง attachment record ในฐานข้อมูล
                    $attachment = $userMessage->attachments()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);

                    // 4. เก็บ ID ของ attachment ไว้
                    $attachmentIds[] = $attachment->id;
                }
            }

            // 5. ส่ง ID ของไฟล์แนบไปให้ Job (ไม่ใช่ path)
            ProcessPromptWithFastAPI::dispatch(
                $this->conversation->id,
                $this->message,
                $attachmentIds // 👈 ส่ง array ของ ID ไปแทน
            );

            $this->isWaitingForResponse = true;

            // 3. รีเซ็ตฟอร์มทันทีเพื่อให้ User ใช้งานต่อได้เลย
            $this->message = '';
            $this->files = [];
            $this->newFiles = [];
            $this->showFileUpload = false;
            $this->resetErrorBag();

            // เราจะไม่รอ $response จาก FastAPI อีกต่อไป และจะไม่สร้าง message ของ 'model' ในนี้
            // --- จบส่วนที่แก้ไข ---
        } catch (\Exception $e) {
            $this->isWaitingForResponse = false;
            Log::error('Error in ChatBox sendMessage: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            // Store error message
            $this->conversation->messages()->create([
                'role' => 'model',
                'content' => 'Sorry, there was an error processing your request: ' . $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        $this->conversation->load(['messages' => function ($query) {
            $query->latest();
        }]);
        $messages = $this->conversation->messages;

//        if ($messages->isNotEmpty()) {
//            $latestMessageRole = $messages->first()->role;
//            $this->isWaitingForResponse = ($latestMessageRole === 'user');
//
//            // ❗️ ย้าย Log เข้ามาไว้ในนี้
//            Log::info('ChatBox Rendering (Has Messages):', [
//                'conversation_id' => $this->conversation->id,
//                'latest_message_role' => $latestMessageRole,
//                'isWaitingForResponse' => $this->isWaitingForResponse,
//            ]);
//        } else {
//            $this->isWaitingForResponse = false;
//
//            // ❗️ เพิ่ม Log สำหรับกรณีที่ยังไม่มีข้อความ
//            Log::info('ChatBox Rendering (No Messages):', [
//                'conversation_id' => $this->conversation->id,
//                'isWaitingForResponse' => $this->isWaitingForResponse,
//            ]);
//        }

        return view('livewire.chat-box', [
            'messages' => $messages,
        ]);
    }
}
