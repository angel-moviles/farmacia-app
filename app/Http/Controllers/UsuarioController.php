<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    public function index()
    {
        try {
            $usuarios = Usuario::with('rol')->get();
            return response()->json($usuarios);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar usuarios'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'clave_usuario' => 'required|unique:usuarios,clave_usuario',
                'nombre' => 'required|string|max:255',
                'a_paterno' => 'required|string|max:255',
                'correo' => 'required|email|unique:usuarios,correo',
                'contrasena' => 'required|min:6',
                'id_rol' => 'required|exists:rols,id_rol',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            $data = $request->all();
            $data['contrasena'] = Hash::make($request->contrasena);
            $data['activo'] = true;

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $nombreArchivo = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/usuarios', $nombreArchivo);
                $data['foto'] = $nombreArchivo;
            }

            $usuario = Usuario::create($data);

            return response()->json([
                "message" => "Usuario creado correctamente",
                "usuario" => $usuario->load('rol')
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return response()->json(Usuario::with('rol')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            // Validar (el correo ignora el ID actual para permitir guardar sin cambiarlo)
            $request->validate([
                'nombre' => 'required|string',
                'correo' => 'required|email|unique:usuarios,correo,' . $id . ',id_usuario',
                'foto' => 'nullable|image|max:2048'
            ]);

            $data = $request->except(['foto', '_method']);

            if ($request->hasFile('foto')) {
                // Eliminar anterior
                if ($usuario->foto) {
                    Storage::delete('public/usuarios/' . $usuario->foto);
                }

                // Guardar nueva
                $file = $request->file('foto');
                $nombreArchivo = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/usuarios', $nombreArchivo);
                $data['foto'] = $nombreArchivo;
            }

            $usuario->update($data);

            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'usuario' => $usuario->fresh(['rol'])
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            if ($usuario->foto) {
                Storage::delete('public/usuarios/' . $usuario->foto);
            }
            $usuario->delete();
            return response()->json(["message" => "Usuario eliminado correctamente"]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar'], 500);
        }
    }
}