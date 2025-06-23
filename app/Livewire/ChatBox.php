<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Repositories\ConversationRepository;
use App\Services\FastAPIService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ChatBox extends Component
{
    use WithFileUploads;

    public Conversation $conversation;
    public string $message = '';
    public $files = [];
    public bool $loading = false;
    public bool $showFileUpload = false;
    public bool $showSuggestedPrompts = false;

    protected $rules = [
        'message' => 'required|string',
        'files.*' => 'nullable|file|max:20480', // 20MB max file size
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
        // แสดง suggested prompts เมื่อเริ่มต้นถ้าไม่มีข้อความใน conversation
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

    public function removeFile($index)
    {
        // Remove file at the specified index
        unset($this->files[$index]);
        $this->files = array_values($this->files); // Re-index array
    }

    public function sendMessage($prompt = null)
    {
        if ($prompt) {
            $this->message = $prompt;
        }

        $this->validate();

        $this->loading = true;
        $this->showSuggestedPrompts = false; // ซ่อน suggested prompts เมื่อส่งข้อความ

        try {
            $fastAPIService = app(FastAPIService::class);
            $conversationRepository = app(ConversationRepository::class);

            // Prepare user message content
            $userMessageContent = $this->message;
            $fileDescriptions = [];
            $uploadedFiles = [];

            // If files are present, prepare them and store metadata
            if (count($this->files) > 0) {
                foreach ($this->files as $file) {
                    // Create proper UploadedFile instances from Livewire temporary files
                    $tempPath = $file->getRealPath();

                    $uploadedFile = new UploadedFile(
                        $tempPath,
                        $file->getClientOriginalName(),
                        $file->getMimeType(),
                        null,
                        true
                    );

                    $uploadedFiles[] = $uploadedFile;

                    $fileDescriptions[] = [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }

                // Add file information to message content
                $userMessageContent = [
                    'text' => $this->message,
//                    'files' => $fileDescriptions
                ];

                // Get response from Fast API with files
                $response = $fastAPIService->sendFilesAndPrompt($uploadedFiles, $this->message, $this->conversation->id);
            } else {
                // No files, just send text prompt
                $response = $fastAPIService->sendPrompt($this->message, $this->conversation->id);
            }

            // Store user message
            $userMessage = $this->conversation->messages()->create([
                'type' => 'user',
//                'content' => is_array($userMessageContent) ? json_encode($userMessageContent) : $userMessageContent,
                'content' => $this->message,
            ]);


            // Store file attachments if any
            if (count($this->files) > 0) {
                foreach ($this->files as $file) {
                    // Save files to permanent storage
                    $path = $file->store('chat_attachments/' . $this->conversation->id, 'public');

                    // Create attachment record (assuming you have an attachments relationship)
                    $userMessage->attachments()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
//                        'size' => $file->getSize(),
                    ]);
                }
            }

            // Store assistant response
            $this->conversation->messages()->create([
                'type' => 'assistant',
                'content' => $response,
            ]);

            // Call define chat name directly
            try {
                // If not defined
                if ($this->conversation->title == "New Conversation") {
                    $chatName = $fastAPIService->defineChatName($this->conversation->id);
                    $conversationRepository->update(['title' => $chatName], $this->conversation->id);
                    Log::info('Successfully defined chat name: ' . $chatName);
                }
            } catch (\Exception $e) {
                Log::error('Failed to define chat name: ' . $e->getMessage());
            }

            // Reset the properties
            $this->message = '';
            $this->files = [];
            $this->showFileUpload = false;

            // Clear the input field via JavaScript
            $this->js("document.getElementById('sendMessageComplete').value = ''");

        } catch (\Exception $e) {
            Log::error('Error in ChatBox sendMessage: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            // Store error message
            $this->conversation->messages()->create([
                'type' => 'assistant',
                'content' => 'Sorry, there was an error processing your request: ' . $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.chat-box', [
            'messages' => $this->conversation->messages()->latest()->get(),
        ]);
    }
}
