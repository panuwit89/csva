<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Services\GradioService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatBox extends Component
{
    use WithFileUploads;

    public Conversation $conversation;
    public string $message = '';
    public $files = [];
    public bool $loading = false;
    public bool $showFileUpload = false;

    protected $rules = [
        'message' => 'required|string',
        'files.*' => 'nullable|file|max:10240', // 10MB max file size
    ];

    public function mount(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function toggleFileUpload()
    {
        $this->showFileUpload = !$this->showFileUpload;
    }

    public function removeFile($index)
    {
        // Remove file at the specified index
        unset($this->files[$index]);
        $this->files = array_values($this->files); // Re-index array
    }

    public function sendMessage()
    {
        $this->validate();

        $this->loading = true;

        try {
            $gradioService = app(GradioService::class);

            // Prepare user message content
            $userMessageContent = $this->message;
            $fileDescriptions = [];

            // If files are present, prepare them and store metadata
            if (count($this->files) > 0) {
                // Convert Livewire temporary files to proper UploadedFile instances
                $uploadedFiles = [];

                foreach ($this->files as $file) {
                    // Store file temporarily for processing
                    $tempPath = $file->store('temp');
                    $fullPath = Storage::path($tempPath);

                    // Create a proper UploadedFile instance
                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $fullPath,
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
                    'files' => $fileDescriptions
                ];

                // Get response from Gradio API with files
                $response = $gradioService->sendFilesAndPrompt($uploadedFiles, $this->message);

                // Clean up temporary files
                foreach ($this->files as $file) {
                    $tempPath = $file->store('temp');
                    Storage::delete($tempPath);
                }
            } else {
                // No files, just send text prompt
                $response = $gradioService->sendPrompt($this->message);
            }

            // Store user message
            $userMessage = $this->conversation->messages()->create([
                'type' => 'user',
                'content' => is_array($userMessageContent) ? json_encode($userMessageContent) : $userMessageContent,
            ]);

            // Store file attachments if any
            if (count($this->files) > 0) {
                foreach ($this->files as $file) {
                    // Save files to permanent storage
                    $path = $file->store('chat_attachments/' . $this->conversation->id, 'public');

                    // Create attachment record (assuming you have an attachments relationship)
                    // You might need to create this relationship and migration
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

            // Reset the property
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
