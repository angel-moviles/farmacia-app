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
                Log::info('=== ACTUALIZANDO USUARIO ID: ' . $id . ' ===');
                
                $usuario = Usuario::findOrFail($id);

                // Validaciones
                $rules = [
                    'nombre' => 'required|string|max:255',
                    'a_paterno' => 'required|string|max:255',
                    'a_materno' => 'nullable|string|max:255',
                    'correo' => 'required|email|unique:usuarios,correo,' . $id . ',id_usuario',
                    'telefono' => 'nullable|string|max:20',
                    'fecha_nacimiento' => 'nullable|date',
                    'sexo' => 'nullable|in:M,F'
                ];

                if ($request->has('id_rol') && $request->id_rol) {
                    $rules['id_rol'] = 'exists:rols,id_rol';
                }

                $request->validate($rules);

                // Preparar datos
                $data = [
                    'nombre' => $request->nombre,
                    'a_paterno' => $request->a_paterno,
                    'a_materno' => $request->a_materno ?? '',
                    'telefono' => $request->telefono ?? '',
                    'correo' => $request->correo,
                ];

                if ($request->has('fecha_nacimiento') && $request->fecha_nacimiento) {
                    $data['fecha_nacimiento'] = $request->fecha_nacimiento;
                }

                if ($request->has('sexo') && $request->sexo) {
                    $data['sexo'] = $request->sexo;
                }

                if ($request->has('id_rol') && $request->id_rol) {
                    $data['id_rol'] = $request->id_rol;
                }

                // PROCESAR FOTO
                // Procesar foto
                if ($request->hasFile('foto')) {
                    $file = $request->file('foto');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    
                    // Guardar en la carpeta pública correcta
                    $file->storeAs('public/usuarios', $filename);
                    
                    // Eliminar foto anterior
                    if ($usuario->foto) {
                        $oldPath = storage_path('app/public/usuarios/' . $usuario->foto);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    
                    $data['foto'] = $filename;
                }

        
                $usuario->update($data);
                
                $usuarioActualizado = Usuario::with('rol')->find($id);
                
                Log::info('Usuario actualizado correctamente');
                Log::info('Foto URL: ' . $usuarioActualizado->foto_url);

                return response()->json([
                    'message' => 'Usuario actualizado correctamente',
                    'usuario' => $usuarioActualizado
                ]);

            } catch (\Exception $e) {
                Log::error('Error al actualizar usuario: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Error al actualizar usuario'
                ], 500);
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