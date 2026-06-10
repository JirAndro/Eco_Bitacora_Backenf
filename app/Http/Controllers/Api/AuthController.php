<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function registrar(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string|min:4'
        ]);
        $correoSimulado = $request->usuario . '@ciidir.mx';
        // Verificamos si la matrícula ya está registrada
        if (User::where('email', $correoSimulado)->exists()) {
            return response()->json(['error' => 'Esta matrícula/usuario ya está registrada.'], 400);
        }
        // Creamos al usuario encriptando la contraseña
        $user = User::create([
            'name' => $request->usuario,
            'email' => $correoSimulado,
            'password' => Hash::make($request->password),
        ]);
        return response()->json([
            'mensaje' => 'Registro exitoso',
            'user_id' => $user->id
        ], 200);
    }
    public function login(Request $request)
    {
        $correoSimulado = $request->usuario . '@ciidir.mx';
        $user = User::where('email', $correoSimulado)->first();
        // Validamos existencia y contraseña
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas.'], 401);
        }
        return response()->json([
            'mensaje' => 'Login exitoso',
            'user_id' => $user->id
        ], 200);
    }
}
