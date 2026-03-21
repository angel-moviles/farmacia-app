<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required'
        ]);

        $usuario = Usuario::with('rol')->where('correo', $request->correo)->first();

        if (!$usuario) {
            return response()->json([
                "message" => "Usuario no encontrado"
            ], 404);
        }

        if (!Hash::check($request->contrasena, $usuario->contrasena)) {
            return response()->json([
                "message" => "Contraseña incorrecta"
            ], 401);
        }

        return response()->json([
            "message" => "Login exitoso",
            "usuario" => $usuario
        ]);
    }
}