<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GradioService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.gradio.url', 'http://host.docker.internal:8001');
    }

    /**
     * Send a text prompt to the Gradio API
     */
    public function sendPrompt(string $prompt): string
    {
        try {
            Log::info('Sending prompt to Gradio API: ' . $prompt);

            $response = Http::timeout(60)->post("{$this->baseUrl}/api/process_prompt", [
                'prompt' => $prompt,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Received response from Gradio API', ['data' => $data]);

                // Handle different response formats
                if (isset($data['result'])) {
                    return is_array($data['result']) ? ($data['result'][0] ?? 'No response') : $data['result'];
                }

                if (is_array($data) && !empty($data)) {
                    return $data[0] ?? 'No response';
                }

                return 'Sorry, I could not process your request. Unexpected response format.';
            } else {
                Log::error('Gradio API error: ' . $response->status() . ' - ' . $response->body());
                return 'There was an error communicating with the AI service. Status: ' . $response->status();
            }
        } catch (\Exception $e) {
            Log::error('Error connecting to Gradio API: ' . $e->getMessage());

            if ($e instanceof ConnectionException) {
                return 'Could not connect to the Gradio API service. Please make sure the Gradio server is running.';
            }

            return 'Could not process your request. Error: ' . $e->getMessage();
        }
    }

    /**
     * Send files and a prompt to the Gradio API
     */
    public function sendFilesAndPrompt(array $files, string $prompt): string
    {
        try {
            Log::info('Sending files and prompt to Gradio API', [
                'fileCount' => count($files),
                'prompt' => $prompt
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
                Log::error('Gradio API error when sending files', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                return 'There was an error communicating with the AI service when processing files. Status: ' . $response->status();
            }
        } catch (\Exception $e) {
            Log::error('Error connecting to Gradio API with files: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return 'Could not connect to the AI service when processing files. Error: ' . $e->getMessage();
        }
    }

}
