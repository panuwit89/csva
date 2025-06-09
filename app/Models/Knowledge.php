<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Knowledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'description',
        'is_active',
        'uploaded_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    public function getFullPathAttribute()
    {
        return storage_path('app/' . $this->file_path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
