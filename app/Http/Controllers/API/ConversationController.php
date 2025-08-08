<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Repositories\ConversationRepository;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        private ConversationRepository $conversationRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation $conversation)
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
        //
    }

    public function getHistory(Conversation $conversation)
    {
        // ใช้ Repository เพื่อดึงข้อมูลทั้งหมดที่ต้องการ
        $history = $this->conversationRepository->getConversationMessageWithAttachments($conversation->id);

        // ส่งข้อมูลกลับไปในรูปแบบ JSON
        return response()->json($history);
    }
}
