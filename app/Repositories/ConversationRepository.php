<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Traits\SimpleCRUD;
use Illuminate\Support\Facades\Storage;

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
                $fileContent = null;
                if (Storage::disk('public')->exists($attachment->path)) {
                    $fileContent = base64_encode(Storage::disk('public')->get($attachment->path));
                }

                return [
                    'original_name' => $attachment->original_name,
                    'mime_type'     => $attachment->mime_type,
                    'content_base64' => $fileContent,
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
