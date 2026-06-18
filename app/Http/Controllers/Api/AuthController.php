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
        'email' => 'required|email|unique:users,email', // Validamos que envíe correo y no esté repetido
        'password' => 'required|string|min:4'
    ]);

    // Creamos al usuario usando el email real
    $user = User::create([
        'name' => $request->usuario,
        'email' => $request->email, // <-- AHORA USA EL DEL APP
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
