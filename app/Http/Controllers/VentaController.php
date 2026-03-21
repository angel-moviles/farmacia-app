<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    public function index()
    {
        try {
            $ventas = Venta::with(['detalles.producto', 'usuario'])
                ->orderBy('fecha', 'desc')
                ->get();
            return response()->json($ventas);
        } catch (\Exception $e) {
            Log::error('Error al obtener ventas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            Log::info('=== NUEVA VENTA ===');
            Log::info('Datos recibidos:', $request->all());

            // Validar datos
            $request->validate([
                'total' => 'required|numeric|min:0',
                'id_usuario' => 'required|exists:usuarios,id_usuario',
                'productos' => 'required|array|min:1',
                'productos.*.id_producto' => 'required|exists:productos,id_producto',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0',
                'productos.*.subtotal' => 'required|numeric|min:0'
            ]);

            // Crear la venta
            $venta = Venta::create([
                'fecha' => now(),
                'total' => $request->total,
                'id_usuario' => $request->id_usuario
            ]);

            Log::info('Venta creada ID: ' . $venta->id_venta);

            // Crear detalles y actualizar stock
            foreach ($request->productos as $productoData) {
                // Verificar stock antes de vender
                $producto = Producto::find($productoData['id_producto']);
                if (!$producto) {
                    throw new \Exception("Producto no encontrado ID: {$productoData['id_producto']}");
                }

                if ($producto->stock < $productoData['cantidad']) {
                    throw new \Exception("Stock insuficiente para: {$producto->nombre}. Disponible: {$producto->stock}, Solicitado: {$productoData['cantidad']}");
                }

                // Crear detalle
                VentaDetalle::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $productoData['id_producto'],
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $productoData['precio_unitario'],
                    'subtotal' => $productoData['subtotal']
                ]);

                Log::info('Detalle creado:', [
                    'producto' => $producto->nombre,
                    'cantidad' => $productoData['cantidad'],
                    'subtotal' => $productoData['subtotal']
                ]);

                // Actualizar stock
                $stockAnterior = $producto->stock;
                $nuevoStock = $stockAnterior - $productoData['cantidad'];
                $producto->update(['stock' => $nuevoStock]);
            }

            DB::commit();

            // Obtener la venta completa con relaciones
            $ventaCompleta = Venta::with(['detalles.producto', 'usuario'])
                ->find($venta->id_venta);

            Log::info('Venta registrada exitosamente');

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada exitosamente',
                'data' => $ventaCompleta
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar venta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $venta = Venta::with(['detalles.producto', 'usuario'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $venta
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada'
            ], 404);
        }
    }
}