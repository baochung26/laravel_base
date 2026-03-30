<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends BaseModel
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'is_featured',
        'views_count',
        'likes_count',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
