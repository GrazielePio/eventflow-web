<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;

// Lógica de Autenticação
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Lógica de Dados (Protegida)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Substitui todas as rotas manuais de eventos por uma única linha
    Route::apiResource('eventos', EventoController::class);
});