<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SincronizacionController;
use App\Http\Controllers\Api\AuthController;

// Endpoint: POST /api/sincronizar
Route::post('/sincronizar', [SincronizacionController::class, 'sincronizar']);

Route::post('/registro', [AuthController::class, 'registrar']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas Privadas (Protegidas por Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Si la app no envía un token válido, Laravel rebotará esta petición automáticamente con un error 401 Unauthorized
    Route::post('/sincronizar', [SincronizacionController::class, 'sincronizar']);
});
