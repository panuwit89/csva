<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['message_id', 'path', 'original_name', 'mime_type'];

    public function message() : BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function getUrl(): string
    {
        return Storage::url($this->path);
    }

    public function getFileSize(): int
    {
        return Storage::exists($this->path) ? Storage::size($this->path) : 0;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isText(): bool
    {
        return str_starts_with($this->mime_type, 'text/');
    }
}
