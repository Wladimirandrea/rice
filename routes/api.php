<?php
// routes/api.php

use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\CaseManagerController;
use App\Http\Controllers\Api\Admin\ScheduleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;

// ── Rutas públicas ──────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});
Route::prefix('auth')->group(function () {
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword']);
});

// ── Rutas protegidas con Sanctum ────────────────────────────
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
});

// ── Ejemplo de rutas protegidas por rol ────────────────────
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('/users', AdminUserController::class);
    Route::get('/users-case-managers', [AdminUserController::class, 'caseManagers']);

    // ── Case Managers — estáticas primero ──
    Route::get('/case-managers/all', [CaseManagerController::class, 'allManagers']);
    Route::get('/case-managers/unassigned-clients', [CaseManagerController::class, 'unassignedClients']);
    Route::get('/case-managers/client/{client}/manager', [CaseManagerController::class, 'clientManager']);
    Route::post('/case-managers/reassign', [CaseManagerController::class, 'reassign']); // ← subir aquí
    Route::get('/case-managers', [CaseManagerController::class, 'index']);

    // ── Con parámetros — al final ──

    Route::get('/case-managers/{user}/clients', [CaseManagerController::class, 'clients']);
    Route::delete('/case-managers/release/{client}', [CaseManagerController::class, 'release']);


    // Module 4 — Schedules
    Route::get('/schedule', [ScheduleController::class, 'index']);
    Route::put('/schedule/{schedule}', [ScheduleController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'role:admin,case_manager'])->prefix('manager')->group(function () {
    // Route::get('/clients', ...);
});

Route::middleware(['auth:sanctum', 'role:client'])->prefix('client')->group(function () {
    // Route::get('/appointments', ...);
});
