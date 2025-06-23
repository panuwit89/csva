<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\FastAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function __construct(
        private FastAPIService $fastAPIService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conversations = Auth::user()->conversations()->latest()->get();

        return view('conversation.index', compact('conversations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $conversation = Auth::user()->conversations()->create([
            'title' => $validated['title'] ?? 'New Conversation',
        ]);

        // Create conversation session in FastAPI
        $this->fastAPIService->createChatSession($conversation->id);

        return redirect()->route('conversation.show', $conversation);
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $messages = $conversation->messages;

        return view('conversation.show', compact('conversation', 'messages'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conversation $conversation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conversation $conversation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $this->fastAPIService->deleteChatSession($conversation->id);

        $conversation->delete();

        return redirect()->route('conversation.index');
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

        // Get response from Fast API
        $responseContent = $this->fastAPIService->sendPrompt($validated['message'], $conversation->id);

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

            // Get response from Fast API
            $responseContent = $this->fastAPIService->sendFilesAndPrompt($uploadedFiles, $validated['message'], $conversation->id);

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
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Failed to process message with files: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to process message with files. Please try again.');
        }
    }
}
