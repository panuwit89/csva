<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Users who have this tag as interest
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tags');
    }

    /**
     * Create slug from name
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->name)) {
                $tag->name = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name')) {
                $tag->name = Str::slug($tag->name);
            }
        });
    }
}
