<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    // auto generated slug saat create/update
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if(empty($category->slug)){
                $category->slug = Str::slug($category->name);
            };
        });

         static::updating(function ($category) {
            if($category->isDirty('name') && empty($category->slug)){
                $category->slug = Str::slug($category->name);
            };
        });
    }

    // relasi: category has many posts
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // accessor: hitung jumlah posts
    public function getPostsCountAttribute(): int
    {
        return $this->posts()->count();
    }

}
