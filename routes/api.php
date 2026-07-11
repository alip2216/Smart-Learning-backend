<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LearningProjectController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LearningLogController;
use App\Http\Controllers\Api\ProfileController;

// Autentikasi Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Butuh Login / Token)
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    // Profile Update
    Route::post('/profile', [ProfileController::class, 'update']);

    // Learning Projects
    Route::get('/learning-projects', [LearningProjectController::class, 'index']);
    Route::post('/learning-projects', [LearningProjectController::class, 'store']);
    Route::delete('/learning-projects/{project}', [LearningProjectController::class, 'destroy']);
    // Anda bisa tambahkan show, update, delete untuk learning-projects nanti di sini

    // Learning Logs (Catatan Belajar)
    Route::get('/learning-projects/{project}/logs', [LearningLogController::class, 'index']);
    Route::post('/learning-projects/{project}/logs', [LearningLogController::class, 'store']);
    Route::put('/learning-projects/{project}/logs/{log}', [LearningLogController::class, 'update']);
    Route::delete('/learning-projects/{project}/logs/{log}', [LearningLogController::class, 'destroy']);
    
    // AI Chat Histories (Mentor)
    Route::get('/learning-projects/{project}/chats', [\App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::post('/learning-projects/{project}/chats', [\App\Http\Controllers\Api\ChatController::class, 'store']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
