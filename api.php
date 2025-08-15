<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public video routes
Route::get('/videos', [VideoController::class, 'index']);
Route::get('/videos/trending', [VideoController::class, 'trending']);
Route::get('/videos/search', [VideoController::class, 'search']);
Route::get('/videos/{id}', [VideoController::class, 'show']);
Route::get('/videos/{id}/comments', [CommentController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/users/{id}/videos', [VideoController::class, 'userVideos']);
Route::get('/categories', [CategoryController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Video routes
    Route::post('/videos', [VideoController::class, 'store']);
    Route::post('/videos/{id}/like', [VideoController::class, 'like']);
    Route::post('/videos/{id}/share', [VideoController::class, 'share']);
    Route::delete('/videos/{id}', [VideoController::class, 'destroy']);
    
    // Comment routes
    Route::post('/videos/{id}/comments', [CommentController::class, 'store']);
    
    // User routes
    Route::post('/users/{id}/follow', [UserController::class, 'follow']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
});