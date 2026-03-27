<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    public function index()
    {
        try {
            $productos = Producto::with(['laboratorio', 'tipoProducto', 'presentacion', 'proveedor'])->get();
            return response()->json($productos);
        } catch (\Exception $e) {
            Log::error('Error al obtener productos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar productos'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('=== CREANDO NUEVO PRODUCTO ===');
            Log::info('Datos recibidos:', $request->all());

            $request->validate([
                'lote' => 'required|string|max:50|unique:productos,lote',
                'codigo_barras' => 'nullable|string|max:50|unique:productos,codigo_barras',
                'nombre' => 'required|string|max:255',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'fecha_produccion' => 'required|date',
                'fecha_caducidad' => 'required|date|after:fecha_produccion',
                'costo' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'id_laboratorio' => 'required|exists:laboratorios,id_laboratorio',
                'id_tipo_producto' => 'required|exists:tipo_productos,id_tipo_producto',
                'id_presentacion' => 'required|exists:presentacions,id_presentacion',
                'id_proveedor' => 'required|exists:proveedors,id_proveedor',
                'activo' => 'boolean'
            ]);

            $data = $request->all();

            // Manejar la foto
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $file->storeAs('productos', $filename, 'public');
                $data['foto'] = $filename;
                Log::info('Foto guardada: ' . $filename);
            }

            $producto = Producto::create($data);

            return response()->json($producto->load(['laboratorio', 'tipoProducto', 'presentacion', 'proveedor']), 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear producto: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);

            $request->validate([
                'lote' => 'required|string|max:50|unique:productos,lote,' . $id . ',id_producto',
                'codigo_barras' => 'nullable|string|max:50|unique:productos,codigo_barras,' . $id . ',id_producto',
                'nombre' => 'required|string|max:255',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'fecha_produccion' => 'required|date',
                'fecha_caducidad' => 'required|date|after:fecha_produccion',
                'costo' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'id_laboratorio' => 'required|exists:laboratorios,id_laboratorio',
                'id_tipo_producto' => 'required|exists:tipo_productos,id_tipo_producto',
                'id_presentacion' => 'required|exists:presentacions,id_presentacion',
                'id_proveedor' => 'required|exists:proveedors,id_proveedor',
                'activo' => 'boolean'
            ]);

            $data = $request->all();

            // Manejar la foto
            if ($request->hasFile('foto')) {
                // Eliminar foto anterior si existe
                if ($producto->foto) {
                    Storage::disk('public')->delete('productos/' . $producto->foto);
                }
                
                $file = $request->file('foto');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $file->storeAs('productos', $filename, 'public');
                $data['foto'] = $filename;
                Log::info('Foto actualizada: ' . $filename);
            }

            $producto->update($data);

            return response()->json($producto->load(['laboratorio', 'tipoProducto', 'presentacion', 'proveedor']));

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar producto: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $producto = Producto::find($id);
            
            if (!$producto) {
                return response()->json([
                    'message' => 'Producto no encontrado'
                ], 404);
            }
            
            // Verificar si el producto tiene ventas asociadas
            if ($producto->ventaDetalles()->count() > 0) {
                // En lugar de eliminar, desactivar el producto
                $producto->update(['activo' => false]);
                
                return response()->json([
                    'message' => 'El producto no se puede eliminar porque tiene ventas asociadas. Se ha desactivado.',
                    'producto' => $producto
                ], 200);
            }
            
            $producto->delete();

            return response()->json([
                'message' => 'Producto eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar producto: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Buscar producto por código de barras
    public function buscarPorCodigoBarras($codigo)
{
        try {
            Log::info('Buscando producto por código: ' . $codigo);
            
            $producto = Producto::with(['laboratorio', 'tipoProducto', 'presentacion', 'proveedor'])
                ->where('codigo_barras', $codigo)
                ->first();
            
            if (!$producto) {
                return response()->json([
                    'message' => 'Producto no encontrado'
                ], 404);
            }
            
            // Asegurar que los valores numéricos sean números
            $producto->precio_venta = (float) $producto->precio_venta;
            $producto->costo = (float) $producto->costo;
            $producto->stock = (int) $producto->stock;
            
            return response()->json($producto);
            
        } catch (\Exception $e) {
            Log::error('Error al buscar producto: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al buscar producto'
            ], 500);
        }
    }
}
