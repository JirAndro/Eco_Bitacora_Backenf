<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SincronizacionController;
use App\Http\Controllers\Api\AuthController;

// Endpoint: POST /api/sincronizar
Route::post('/sincronizar', [SincronizacionController::class, 'sincronizar']);

Route::post('/registro', [AuthController::class, 'registrar']);
Route::post('/login', [AuthController::class, 'login']);
