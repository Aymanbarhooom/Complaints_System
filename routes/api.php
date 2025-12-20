<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ComplaintHistoryController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/allcomplaints',[ComplaintController::class, 'allComplaints']);
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::post('/complaints/{id}/open', [ComplaintController::class, 'open']);
    Route::post('/complaints/{id}/status', [ComplaintController::class, 'updateStatus']);

    Route::post('/complaints/{id}/comments', [CommentController::class, 'store']);

    Route::get('/complaints/{id}/history', [ComplaintHistoryController::class, 'index']);

    Route::get('/agencies', [AgencyController::class, 'index']);
    Route::post('/agencies', [AgencyController::class, 'store']);
    Route::delete('/agencies/{id}', [AgencyController::class, 'destroy']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{id}', [UserController::class, 'remove']);
});
