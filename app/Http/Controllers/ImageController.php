<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    public function getUsuarioImage($filename)
    {
        Log::info('Buscando imagen: ' . $filename);
        
        // Buscar en storage/app/public/usuarios/
        $path = storage_path('app/public/usuarios/' . $filename);
        
        if (file_exists($path)) {
            Log::info('Imagen encontrada en: ' . $path);
            return response()->file($path);
        }
        
        Log::error('Imagen no encontrada: ' . $filename);
        abort(404);
    }
    public function getProductoImage($filename)
        {
            Log::info('Buscando imagen de producto: ' . $filename);
            
            $path = storage_path('app/public/productos/' . $filename);
            
            if (file_exists($path)) {
                Log::info('Imagen de producto encontrada en: ' . $path);
                return response()->file($path);
            }
            
            Log::error('Imagen de producto no encontrada: ' . $filename);
            abort(404);
        }
}