<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    public function getUsuarioImage($filename)
    {
        // Buscar en storage/app/public/usuarios/
        $path = storage_path('app/public/usuarios/' . $filename);
        
        if (file_exists($path)) {
            return response()->file($path);
        }
        
        // Si no existe, devolver imagen por defecto
        $defaultPath = public_path('images/default-avatar.png');
        if (file_exists($defaultPath)) {
            return response()->file($defaultPath);
        }
        
        abort(404);
    }
}