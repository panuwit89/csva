<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Repositories\Traits\SimpleCRUD;

class ConversationRepository
{
    use SimpleCRUD;

    private string $model = Conversation::class;
}
