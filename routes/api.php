<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ComplaintHistoryController;
use App\Http\Controllers\SystemLogController;

Route::post('/register', [AuthController::class, 'registerCitizen']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/allcomplaints',[ComplaintController::class, 'allComplaints']);
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::post('/comments/{id}', [CommentController::class, 'store']);
    Route::post('/complaints/{id}/assignToMe', [ComplaintController::class, 'assignToMe']);
    Route::post('/complaints/{id}/release', [ComplaintController::class, 'release']);
    Route::post('/complaints/{id}/status', [ComplaintController::class, 'updateStatus']);
    Route::get('/complaints/{id}/comments', [CommentController::class, 'index']);

    Route::get('/complaints/{id}/history', [ComplaintHistoryController::class, 'index']);

    Route::get('/agencies', [AgencyController::class, 'index']);
    Route::post('/agencies', [AgencyController::class, 'store']);
    Route::delete('/agencies/{id}', [AgencyController::class, 'destroy']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/addEmployee', [UserController::class, 'addEmployee']);
    Route::delete('/users/{id}', [UserController::class, 'remove']);
});
Route::middleware(['auth:sanctum', 'system.log'])->group(function () {
        Route::get('/system-logs', [SystemLogController::class, 'index']);
        Route::get('/system-logs/statistics', [SystemLogController::class, 'statistics']);
        Route::get('/system-logs/my-activity', [SystemLogController::class, 'userActivity']);
});