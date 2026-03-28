<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 5 kategori
       $categories = Category::factory(5)->create();

       // 20 post
       foreach ($categories as $category) {
           Post::factory(4)->create([
            'category_id' => $category->id
           ]);
       }
    }
}
