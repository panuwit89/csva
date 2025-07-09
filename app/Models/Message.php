<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'role', 'content'];

    public function conversation() : BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function attachments() : HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function hasAttachments(): bool
    {
        return $this->attachments()->count() > 0;
    }
}
