<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\GradioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    protected GradioService $gradioService;

    public function __construct(GradioService $gradioService)
    {
        $this->gradioService = $gradioService;
    }

    public function index()
    {
        $conversations = Auth::user()->conversations()->latest()->get();

        return view('chat.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        // Make sure the user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $messages = $conversation->messages;

        return view('chat.show', compact('conversation', 'messages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $conversation = Auth::user()->conversations()->create([
            'title' => $validated['title'] ?? 'New Conversation',
        ]);

        return redirect()->route('chat.show', $conversation);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        // Make sure the user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        // Store user message
        $message = $conversation->messages()->create([
            'type' => 'user',
            'content' => $validated['message'],
        ]);

        // Get response from Gradio API
        $responseContent = $this->gradioService->sendPrompt($validated['message']);

        // Store assistant response
        $response = $conversation->messages()->create([
            'type' => 'assistant',
            'content' => $responseContent,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'user_message' => $message,
                'assistant_response' => $response,
            ]);
        }

        return back();
    }

    public function sendMessageWithFiles(Request $request, Conversation $conversation)
    {
        // Make sure the user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        // Validate the request
        $validated = $request->validate([
            'message' => 'required|string',
            'files' => 'required|array',
            'files.*' => 'required|file|max:10240', // 10MB max file size
        ]);

        try {
            // Log for debugging
            Log::info('Received message with files', [
                'fileCount' => count($request->file('files')),
                'message' => $validated['message'],
            ]);

            // Store uploaded files and create file paths array
            $uploadedFiles = [];
            $fileDescriptions = [];

            foreach ($request->file('files') as $file) {
                // Store the file
                $path = $file->store('uploads/' . $conversation->id, 'public');
                $uploadedFiles[] = $file;

                // Add file information to message content
                $fileDescriptions[] = [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'path' => Storage::url($path)
                ];
            }

            // Create user message content with file information
            $userMessageContent = [
                'text' => $validated['message'],
                'files' => $fileDescriptions
            ];

            // Store user message with file information
            $message = $conversation->messages()->create([
                'type' => 'user',
                'content' => json_encode($userMessageContent),
            ]);

            // Get response from Gradio API
            $responseContent = $this->gradioService->sendFilesAndPrompt($uploadedFiles, $validated['message']);

            // Store assistant response
            $response = $conversation->messages()->create([
                'type' => 'assistant',
                'content' => $responseContent,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'user_message' => $message,
                    'assistant_response' => $response,
                ]);
            }

            return back();
        } catch (\Exception $e) {
            Log::error('Error processing message with files: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Failed to process message with files: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to process message with files. Please try again.');
        }
    }
}
