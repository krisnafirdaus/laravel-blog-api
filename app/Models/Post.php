<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'status',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // auto generated slug saat create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if(empty($post->slug)){
                $post->slug = Str::slug($post->title);
            };
        });
    }

    // relasi post belongs to category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // scope: hanya post yang published
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    // scope hanya draft
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // accessor: excerpt dari content
    public function getExcerptAttribute(): string 
    {
        return Str::limit(strip_tags($this->content), 150);
    }

    // method publish post
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

}
