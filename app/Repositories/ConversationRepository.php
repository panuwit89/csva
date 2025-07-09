<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Traits\SimpleCRUD;

class ConversationRepository
{
    use SimpleCRUD;

    private string $model = Conversation::class;

    public function getConversationMessage(int $conversationId)
    {
        return Message::where('conversation_id', $conversationId)
            ->select('role', 'content')
            ->get();
    }
}
