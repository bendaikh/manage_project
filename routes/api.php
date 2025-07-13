<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiChatController;

// AI Chat API Route
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/ai-chat', [AiChatController::class, 'chat']);
}); 