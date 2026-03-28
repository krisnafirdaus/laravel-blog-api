<?php

use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('categories', CategoryController::class);
});

// Route::prefix('v2')->group(function () {
//     Route::apiResource('categories', CategoryController::class);
// });