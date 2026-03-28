<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Category Routes
    Route::apiResource('categories', CategoryController::class);
    
    // Nested Route: Posts dalam Category
    Route::get('/categories/{category}/posts', [CategoryController::class, 'posts']);
    
    // Post Routes
    Route::apiResource('posts', PostController::class);
    
    // Custom Route: Publish Post
    Route::post('/posts/{post}/publish', [PostController::class, 'publish']);
});