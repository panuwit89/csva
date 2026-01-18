<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
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
                'icon' => '🏫',
                'title' => 'หลักสูตรวิทย์คอม',
                'prompts' => [
                    'ขอรายละเอียดโครงสร้างหลักสูตรวิทยาการคอมพิวเตอร์หน่อย',
                    'ภาควิทย์คอมมีวิชาอะไรเปิดบ้าง',
                    'ต้องเรียนกี่หน่วยกิต'
                ]
            ],
            [
                'icon' => '📚',
                'title' => 'วิชาศึกษาทั่วไป',
                'prompts' => [
                    'มีวิชาศึกษาทั่วไปที่น่าสนใจไหม',
                    'แนะนำวิชาศึกษาทั่วไปในแต่ละหมวดหน่อย'
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
        $this->showSuggestedPrompts = false;

        try {
            $userMessage = $this->conversation->messages()->create([
                'role' => 'user',
                'content' => $this->message,
            ]);

            $attachmentIds = [];
            if (count($this->files) > 0) {
                foreach ($this->files as $file) {
                    $path = $file->store('chat_attachments/' . $this->conversation->id, 'public');
                    $attachment = $userMessage->attachments()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                    $attachmentIds[] = $attachment->id;
                }
            }

            ProcessPromptWithFastAPI::dispatch(
                $this->conversation->id,
                $this->message,
                $attachmentIds
            );

            $this->isWaitingForResponse = true;

            $this->message = '';
            $this->files = [];
            $this->newFiles = [];
            $this->showFileUpload = false;
            $this->resetErrorBag();

        } catch (\Exception $e) {
            $this->isWaitingForResponse = false;
            Log::error('Error in ChatBox sendMessage: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    public function checkForResponse()
    {
        // Query ที่ Message model โดยตรงเพื่อความแม่นยำ
        // และเรียงด้วย 'id' เพื่อให้แน่ใจว่าจะได้ข้อความล่าสุดจริงๆ
        $latestMessage = Message::where('conversation_id', $this->conversation->id)
            ->latest('id')
            ->first();

        if ($latestMessage) {
            Log::debug('Latest message found: ID ' . $latestMessage->id . ', Role: ' . $latestMessage->role);

            if ($this->isWaitingForResponse && $latestMessage->role === 'model') {
                Log::debug('CONDITION MET: Role is "model". Setting isWaitingForResponse to false.');
                $this->isWaitingForResponse = false;

                // บอกให้ Livewire โหลด message ใหม่มาแสดงผล
                $this->dispatch('messageReceived');
            } else {
                Log::debug('Condition NOT met.');
            }
        } else {
            Log::debug('No messages found for this conversation.');
        }
    }

    public function render()
    {
        $messages = $this->conversation->messages()->latest('id')->get();

        return view('livewire.chat-box', [
            'messages' => $messages,
        ]);
    }
}
