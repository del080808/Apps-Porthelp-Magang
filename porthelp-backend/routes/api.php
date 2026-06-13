<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TeknisiController;
use App\Http\Controllers\NotificationController;

// Public
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected semua role
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',     [AuthController::class, 'me']);

    // Tickets — semua role bisa akses, filter di controller
    Route::get('/tickets',              [TicketController::class, 'index']);
    Route::get('/tickets/{id}',         [TicketController::class, 'show']);

    // Pelapor only
    Route::middleware('role:pelapor')->group(function () {
        Route::post('/tickets', [TicketController::class, 'store']);
    });

    // Teknisi & Admin
    Route::middleware('role:teknisi,admin')->group(function () {
        Route::put('/tickets/{id}', [TicketController::class, 'update']);
    });

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::post('/tickets/{id}/assign', [TicketController::class, 'assign']);
        Route::delete('/tickets/{id}',      [TicketController::class, 'destroy']);
        Route::get('/teknisi',              [TeknisiController::class, 'index']);
        Route::post('/teknisi',             [TeknisiController::class, 'store']);
        Route::delete('/teknisi/{id}',      [TeknisiController::class, 'destroy']);
    });

    // Notifikasi — semua role
    Route::get('/notifications',           [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all',  [NotificationController::class, 'markAllAsRead']);
});