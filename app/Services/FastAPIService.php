<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FastAPIService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.fastapi.url', 'http://host.docker.internal:8001');
    }

    /**
     * Send a text prompt to the Fast API
     */
    public function sendPrompt(string $prompt, int $conv_id): string
    {
        try {
            Log::info('Sending prompt to Fast API: ' . $prompt);

            // Sending prompt and conversation id to fast api endpoint
            $response = Http::timeout(60)->post("{$this->baseUrl}/api/process_prompt", [
                'prompt' => $prompt,
                'conv_id' => $conv_id,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Received response from Fast API', ['data' => $data]);

                // Handle different response formats
                if (isset($data['result'])) {
                    return is_array($data['result']) ? ($data['result'][0] ?? 'No response') : $data['result'];
                }

                if (is_array($data) && !empty($data)) {
                    return $data[0] ?? 'No response';
                }

                return 'Sorry, I could not process your request. Unexpected response format.';
            } else {
                Log::error('Fast API error: ' . $response->status() . ' - ' . $response->body());
                return 'There was an error communicating with the AI service. Status: ' . $response->status();
            }
        } catch (\Exception $e) {
            Log::error('Error connecting to Fast API: ' . $e->getMessage());

            if ($e instanceof ConnectionException) {
                return 'Could not connect to the Fast API service. Please make sure the Fast API server is running.';
            }

            return 'Could not process your request. Error: ' . $e->getMessage();
        }
    }

    /**
     * Send files and a prompt to the Fast API
     */
    public function sendFilesAndPrompt(array $files, string $prompt, int $conv_id): string
    {
        try {
            Log::info('Sending files and prompt to Fast API', [
                'fileCount' => count($files),
                'prompt' => $prompt,
                'conv_id' => $conv_id,
            ]);

            // Validate files
            $validFiles = array_filter($files, function($file) {
                return $file instanceof UploadedFile && $file->isValid() && file_exists($file->getRealPath());
            });

            if (empty($validFiles)) {
                Log::error('No valid files to send');
                return 'No valid files were provided.';
            }

            // Create multipart request
            $http = Http::timeout(120)->asMultipart();

            // Add the prompt first
            $http->attach('custom_prompt', $prompt);

            // Add each file to the request
            foreach ($validFiles as $index => $file) {
                $filePath = $file->getRealPath();

                if (!file_exists($filePath)) {
                    Log::error("File does not exist: " . $filePath);
                    continue;
                }

                $fileContents = file_get_contents($filePath);
                if ($fileContents === false) {
                    Log::error("Could not read file: " . $file->getClientOriginalName());
                    continue;
                }

                // Use attach method for files
                $http->attach(
                    'files',
                    $fileContents,
                    $file->getClientOriginalName(),
                    ['Content-Type' => $file->getMimeType()]
                );
            }

            // Add the conversation id
            $http->attach('conv_id', $conv_id);

            // Log the request details for debugging
            Log::info('Prepared multipart request', [
                'validFileCount' => count($validFiles)
            ]);

            // Send the request
            $response = $http->post("{$this->baseUrl}/api/process_files_and_prompt");

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Received file processing response', [
                    'success' => true,
                    'response' => $data
                ]);

                if (isset($data['result'])) {
                    return is_array($data['result']) ? ($data['result'][0] ?? 'No response') : $data['result'];
                }

                if (is_array($data) && !empty($data)) {
                    return $data[0] ?? 'No response';
                }

                return 'Sorry, I could not process your request. Unexpected response format.';
            } else {
                Log::error('Fast API error when sending files', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                return 'There was an error communicating with the AI service when processing files. Status: ' . $response->status();
            }
        } catch (\Exception $e) {
            Log::error('Error connecting to Fast API with files: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return 'Could not connect to the AI service when processing files. Error: ' . $e->getMessage();
        }
    }

    /**
     * Create a new conversation session
     */
    public function createChatSession(int $conv_id): bool
    {
        try {
            Log::info('Creating conversation session: ' . $conv_id);

            $response = Http::timeout(10)->post("{$this->baseUrl}/api/create_chat", [
                'conv_id' => $conv_id,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Chat session created successfully', ['data' => $data]);
                return true;
            } else {
                Log::error('Failed to create conversation session: ' . $response->status() . ' - ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error creating conversation session: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Define a chat name
     */
    public function defineChatName(int $conv_id): string
    {
        try {
            Log::info('Defining chat name for conversation: ' . $conv_id);

            $response = Http::timeout(30)->post("{$this->baseUrl}/api/define_chat_name", [
                'conv_id' => $conv_id,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Received response from Fast API', ['data' => $data]);

                // Handle different response formats
                if (isset($data['result'])) {
                    return is_array($data['result']) ? ($data['result'][0] ?? 'No response') : $data['result'];
                }

                if (is_array($data) && !empty($data)) {
                    return $data[0] ?? 'No response';
                }

                return 'Sorry, I could not process your request. Unexpected response format.';
            } else {
                Log::error('Fast API error: ' . $response->status() . ' - ' . $response->body());
                return 'There was an error communicating with the AI service. Status: ' . $response->status();
            }
        } catch (\Exception $e) {
            Log::error('Error connecting to Fast API: ' . $e->getMessage());

            if ($e instanceof ConnectionException) {
                return 'Could not connect to the Fast API service. Please make sure the Fast API server is running.';
            }

            return 'Could not process your request. Error: ' . $e->getMessage();
        }
    }

    /**
     * Delete a conversation session
     */
    public function deleteChatSession(int $conv_id): bool
    {
        try {
            Log::info('Deleting conversation session: ' . $conv_id);

            $response = Http::timeout(10)->delete("{$this->baseUrl}/api/delete_chat/{$conv_id}");

            if ($response->successful()) {
                Log::info('Chat session deleted successfully');
                return true;
            } else {
                Log::error('Failed to delete conversation session: ' . $response->status() . ' - ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error deleting conversation session: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Refresh AI knowledge base
     */
    public function refreshKnowledge(): array
    {
        try {
            Log::info('Refreshing AI knowledge base');

            // Check refresh status
            $statusResponse = Http::timeout(10)->get("{$this->baseUrl}/api/refresh_status");

            if ($statusResponse->successful()) {
                $status = $statusResponse->json();
                if ($status['is_running']) {
                    return [
                        'success' => false,
                        'message' => 'Knowledge refresh is already in progress',
                        'data' => $status
                    ];
                }
            }

            // Start refreshing
            $response = Http::timeout(10)->post("{$this->baseUrl}/api/refresh_knowledge", [
                'force' => true
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('AI knowledge base refreshed successfully', ['data' => $data]);

                $filesProcessed = $data['files_processed'] ?? 0;
                $message = "AI knowledge base refreshed successfully!";

                return [
                    'success' => true,
                    'message' => $message,
                    'data' => $data
                ];
            } else {
                $errorMessage = 'Failed to refresh AI knowledge base. Status: ' . $response->status();
                Log::error($errorMessage . ' - Response: ' . $response->body());

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => null
                ];
            }
        } catch (\Exception $e) {
            $errorMessage = 'Error refreshing AI knowledge base: ' . $e->getMessage();
            Log::error($errorMessage);

            return [
                'success' => false,
                'message' => $errorMessage,
                'data' => null
            ];
        }
    }
}
