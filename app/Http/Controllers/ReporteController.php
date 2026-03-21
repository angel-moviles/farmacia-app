<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\VentaDetalle;
use App\Models\Laboratorio;
use App\Models\TipoProducto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReporteController extends Controller
{
    /**
     * Reporte de ventas por período
     */
    public function ventasPorPeriodo(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            ]);

            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

            // Ventas por día
            $ventasPorDia = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->select(
                    DB::raw('DATE(fecha) as fecha'),
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha')
                ->get();

            // Totales del período
            $totales = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->select(
                    DB::raw('COUNT(*) as total_ventas'),
                    DB::raw('SUM(total) as total_ingresos'),
                    DB::raw('AVG(total) as ticket_promedio')
                )
                ->first();

            // Productos más vendidos en el período
            $productosMasVendidos = VentaDetalle::join('ventas', 'venta_detalles.id_venta', '=', 'ventas.id_venta')
                ->join('productos', 'venta_detalles.id_producto', '=', 'productos.id_producto')
                ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
                ->select(
                    'productos.nombre',
                    DB::raw('SUM(venta_detalles.cantidad) as cantidad_vendida'),
                    DB::raw('SUM(venta_detalles.subtotal) as total_ingresos')
                )
                ->groupBy('productos.id_producto', 'productos.nombre')
                ->orderByDesc('cantidad_vendida')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'ventas_por_dia' => $ventasPorDia,
                'totales' => $totales,
                'productos_mas_vendidos' => $productosMasVendidos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte'
            ], 500);
        }
    }

    /**
     * Reporte de productos más vendidos
     */
    public function productosMasVendidos(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'limite' => 'nullable|integer|min:1|max:50'
            ]);

            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
            $limite = $request->limite ?? 10;

            $productos = VentaDetalle::join('ventas', 'venta_detalles.id_venta', '=', 'ventas.id_venta')
                ->join('productos', 'venta_detalles.id_producto', '=', 'productos.id_producto')
                ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
                ->select(
                    'productos.nombre',
                    'productos.id_producto',
                    DB::raw('COALESCE(SUM(venta_detalles.cantidad), 0) as cantidad'),
                    DB::raw('COALESCE(SUM(venta_detalles.subtotal), 0) as total')
                )
                ->groupBy('productos.id_producto', 'productos.nombre')
                ->orderByDesc('cantidad')
                ->limit($limite)
                ->get();

            $totalCantidad = $productos->sum('cantidad');
            $totalIngresos = $productos->sum('total');

            return response()->json([
                'success' => true,
                'productos' => $productos,
                'total_cantidad' => $totalCantidad,
                'total_ingresos' => $totalIngresos
            ]);
        } catch (\Exception $e) {
            Log::error('Error en reporte de productos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de inventario
     */
    /**
 * Reporte de inventario
 */
   public function inventario(Request $request)
    {
        try {
            $productos = Producto::with(['laboratorio', 'tipoProducto'])
                ->get()
                ->map(function($producto) {
                    $estado = 'normal';
                    if ($producto->stock <= 0) {
                        $estado = 'sin_stock';
                    } elseif ($producto->stock <= $producto->stock_minimo) {
                        $estado = 'bajo';
                    }
                    
                    return [
                        'id' => $producto->id_producto,
                        'nombre' => $producto->nombre,
                        'lote' => $producto->lote,
                        'stock' => $producto->stock,
                        'stock_minimo' => $producto->stock_minimo,
                        'precio' => $producto->precio_venta,
                        'laboratorio' => $producto->laboratorio->nombre ?? 'N/A',
                        'tipo' => $producto->tipoProducto->nombre ?? 'N/A',
                        'estado' => $estado,
                        'fecha_caducidad' => $producto->fecha_caducidad
                    ];
                });

            $resumen = [
                'total_productos' => $productos->count(),
                'con_stock' => $productos->where('stock', '>', 0)->count(),
                'sin_stock' => $productos->where('stock', '<=', 0)->count(),
                'stock_bajo' => $productos->where('estado', 'bajo')->count(),
                'valor_total' => $productos->sum(function($p) {
                    return $p['stock'] * $p['precio'];
                })
            ];

            return response()->json([
                'success' => true,
                'productos' => $productos,
                'resumen' => $resumen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de inventario'
            ], 500);
        }
    }
    /**
     * Reporte de ventas por usuario
     */
    public function ventasPorUsuario(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
            ]);

            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

            $usuarios = Usuario::with('rol')->get();
            
            $ventasPorUsuario = [];
            $totalVentas = 0;
            $totalIngresos = 0;

            foreach ($usuarios as $usuario) {
                $ventas = Venta::where('id_usuario', $usuario->id_usuario)
                    ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->get();

                $cantidadVentas = $ventas->count();
                $totalUsuario = $ventas->sum('total');

                if ($cantidadVentas > 0 || $totalUsuario > 0) {
                    $ventasPorUsuario[] = [
                        'usuario' => $usuario->nombre . ' ' . $usuario->a_paterno,
                        'rol' => $usuario->rol->nombre ?? 'N/A',
                        'cantidad_ventas' => $cantidadVentas,
                        'total_ventas' => (float)$totalUsuario
                    ];

                    $totalVentas += $cantidadVentas;
                    $totalIngresos += $totalUsuario;
                }
            }

            return response()->json([
                'success' => true,
                'ventas_por_usuario' => $ventasPorUsuario,
                'totales' => [
                    'total_usuarios' => count($ventasPorUsuario),
                    'total_ventas' => $totalVentas,
                    'total_ingresos' => $totalIngresos
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en reporte por usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte por usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
 * Reporte de movimientos de inventario
 */
    public function movimientosInventario(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'tipo' => 'nullable|in:todos,entradas,salidas'
            ]);

            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
            $movimientos = [];

            // Salidas por ventas
            if ($request->tipo !== 'entradas') {
                $ventas = VentaDetalle::join('ventas', 'venta_detalles.id_venta', '=', 'ventas.id_venta')
                    ->join('productos', 'venta_detalles.id_producto', '=', 'productos.id_producto')
                    ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
                    ->select(
                        'ventas.fecha',
                        'productos.nombre as producto',
                        'venta_detalles.cantidad',
                        'venta_detalles.subtotal as total',
                        DB::raw("'Salida' as tipo"),
                        DB::raw("'Venta' as motivo")
                    )
                    ->get();

                foreach ($ventas as $venta) {
                    $movimientos[] = [
                        'fecha' => $venta->fecha,
                        'producto' => $venta->producto,
                        'cantidad' => (int)$venta->cantidad,
                        'total' => (float)$venta->total,
                        'tipo' => $venta->tipo,
                        'motivo' => $venta->motivo
                    ];
                }
            }

            // Aquí podrías agregar entradas de inventario (compras a proveedores)
            // cuando tengas esa funcionalidad

            // Ordenar por fecha (más reciente primero)
            usort($movimientos, function($a, $b) {
                return strtotime($b['fecha']) - strtotime($a['fecha']);
            });

            $totalSalidas = collect($movimientos)->where('tipo', 'Salida')->sum('cantidad');
            $totalEntradas = collect($movimientos)->where('tipo', 'Entrada')->sum('cantidad');

            return response()->json([
                'success' => true,
                'movimientos' => $movimientos,
                'resumen' => [
                    'total_movimientos' => count($movimientos),
                    'total_salidas' => $totalSalidas,
                    'total_entradas' => $totalEntradas
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en reporte de movimientos: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de movimientos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}