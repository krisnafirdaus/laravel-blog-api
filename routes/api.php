<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me' , [AuthController::class, 'me']);
    }); 
    
    // Category Routes
    Route::apiResource('categories', CategoryController::class);
    
    // Nested Route: Posts dalam Category
    Route::get('/categories/{category}/posts', [CategoryController::class, 'posts']);
    
    // Post Routes
    Route::apiResource('posts', PostController::class);
    
    // Custom Route: Publish Post
    Route::post('/posts/{post}/publish', [PostController::class, 'publish']);
});