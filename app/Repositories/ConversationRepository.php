<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Traits\SimpleCRUD;

class ConversationRepository
{
    use SimpleCRUD;

    private string $model = Conversation::class;

    public function getConversationMessageWithAttachments(int $conversationId)
    {
        $conversation = Conversation::where('id', $conversationId)->first();

        $messages = $conversation->messages()
            ->with('attachments')
            ->get();

        return $messages->map(function ($message) {
            $attachmentsData = $message->attachments->map(function ($attachment) {
                return [
                    'url'           => $attachment->getUrl(),
                    'original_name' => $attachment->original_name,
                    'mime_type'     => $attachment->mime_type,
                ];
            });

            return [
                'role'        => $message->role,
                'content'     => $message->content,
                'attachments' => $attachmentsData->toArray(),
            ];
        });
    }
}
