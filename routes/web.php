<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\Web\DashboardController;

// Cuando alguien entre a midominio.com/dashboard, ejecutamos el controlador
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/dashboard/exportar', [DashboardController::class, 'exportar']);
