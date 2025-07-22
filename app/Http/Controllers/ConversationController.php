<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Repositories\ConversationRepository;
use App\Services\FastAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function __construct(
        private FastAPIService $fastAPIService,
        private ConversationRepository $conversationRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conversations = Auth::user()->conversations()->latest('updated_at')->get();

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
}
