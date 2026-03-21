<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Proveedor;
use App\Models\Laboratorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Fechas
            $hoy = now()->format('Y-m-d');
            $inicioMes = now()->startOfMonth()->format('Y-m-d');
            $inicioSemana = now()->startOfWeek()->format('Y-m-d');

            // Estadísticas generales
            $stats = [
                // Usuarios
                'total_usuarios' => Usuario::count(),
                'usuarios_activos' => Usuario::where('activo', true)->count(),
                'usuarios_inactivos' => Usuario::where('activo', false)->count(),
                
                // Productos
                'total_productos' => Producto::count(),
                'productos_activos' => Producto::where('activo', true)->count(),
                'productos_inactivos' => Producto::where('activo', false)->count(),
                'productos_con_stock' => Producto::where('stock', '>', 0)->count(),
                'productos_sin_stock' => Producto::where('stock', '<=', 0)->count(),
                'stock_total' => Producto::sum('stock'),
                'valor_inventario' => Producto::sum(DB::raw('stock * precio_venta')),
                'costo_inventario' => Producto::sum(DB::raw('stock * costo')),
                
                // Ventas
                'total_ventas' => Venta::count(),
                'total_ingresos' => Venta::sum('total'),
                'ventas_hoy' => Venta::whereDate('fecha', $hoy)->count(),
                'ingresos_hoy' => Venta::whereDate('fecha', $hoy)->sum('total'),
                'ventas_semana' => Venta::whereDate('fecha', '>=', $inicioSemana)->count(),
                'ingresos_semana' => Venta::whereDate('fecha', '>=', $inicioSemana)->sum('total'),
                'ventas_mes' => Venta::whereDate('fecha', '>=', $inicioMes)->count(),
                'ingresos_mes' => Venta::whereDate('fecha', '>=', $inicioMes)->sum('total'),
                'ticket_promedio' => Venta::avg('total') ?? 0,
            ];

            // Producto más vendido
            $productoMasVendido = DB::table('venta_detalles')
                ->join('productos', 'venta_detalles.id_producto', '=', 'productos.id_producto')
                ->select(
                    'productos.nombre',
                    'productos.precio_venta',
                    DB::raw('SUM(venta_detalles.cantidad) as total_vendido'),
                    DB::raw('SUM(venta_detalles.subtotal) as total_ingresos')
                )
                ->groupBy('productos.id_producto', 'productos.nombre', 'productos.precio_venta')
                ->orderByDesc('total_vendido')
                ->first();

            // Categorías más vendidas
            $categoriasMasVendidas = DB::table('venta_detalles')
                ->join('productos', 'venta_detalles.id_producto', '=', 'productos.id_producto')
                ->join('tipo_productos', 'productos.id_tipo_producto', '=', 'tipo_productos.id_tipo_producto')
                ->select(
                    'tipo_productos.nombre as categoria',
                    DB::raw('SUM(venta_detalles.cantidad) as total_vendido'),
                    DB::raw('SUM(venta_detalles.subtotal) as total_ingresos')
                )
                ->groupBy('tipo_productos.id_tipo_producto', 'tipo_productos.nombre')
                ->orderByDesc('total_vendido')
                ->limit(5)
                ->get();

            // Laboratorios más vendidos
            $laboratoriosMasVendidos = DB::table('venta_detalles')
                ->join('productos', 'venta_detalles.id_producto', '=', 'productos.id_producto')
                ->join('laboratorios', 'productos.id_laboratorio', '=', 'laboratorios.id_laboratorio')
                ->select(
                    'laboratorios.nombre as laboratorio',
                    DB::raw('SUM(venta_detalles.cantidad) as total_vendido')
                )
                ->groupBy('laboratorios.id_laboratorio', 'laboratorios.nombre')
                ->orderByDesc('total_vendido')
                ->limit(5)
                ->get();

            // Ventas por hora (para hoy)
            $ventasPorHora = DB::table('ventas')
                ->whereDate('fecha', $hoy)
                ->select(
                    DB::raw('HOUR(fecha) as hora'),
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(DB::raw('HOUR(fecha)'))
                ->orderBy('hora')
                ->get();

            // Ventas por día de la semana
            $ventasPorDiaSemana = DB::table('ventas')
                ->whereMonth('fecha', now()->month)
                ->select(
                    DB::raw('DAYOFWEEK(fecha) as dia'),
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(DB::raw('DAYOFWEEK(fecha)'))
                ->orderBy('dia')
                ->get();

            // Últimas 10 ventas
            $ultimasVentas = Venta::with(['usuario', 'detalles'])
                ->orderBy('fecha', 'desc')
                ->limit(10)
                ->get()
                ->map(function($venta) {
                    return [
                        'id' => $venta->id_venta,
                        'fecha' => $venta->fecha->format('d/m/Y H:i'),
                        'usuario' => $venta->usuario->nombre . ' ' . $venta->usuario->a_paterno,
                        'total' => $venta->total,
                        'productos' => $venta->detalles->count()
                    ];
                });

            // Alertas
            $alertas = [
                'stock_bajo' => Producto::whereRaw('stock <= stock_minimo AND stock > 0')
                    ->select('id_producto', 'nombre', 'stock', 'stock_minimo')
                    ->get(),
                'sin_stock' => Producto::where('stock', '<=', 0)
                    ->select('id_producto', 'nombre', 'stock')
                    ->get(),
                'proximos_caducar' => Producto::where('fecha_caducidad', '<=', now()->addDays(30))
                    ->where('fecha_caducidad', '>=', now())
                    ->select('id_producto', 'nombre', 'fecha_caducidad')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'producto_mas_vendido' => $productoMasVendido,
                'categorias_mas_vendidas' => $categoriasMasVendidas,
                'laboratorios_mas_vendidos' => $laboratoriosMasVendidos,
                'ventas_por_hora' => $ventasPorHora,
                'ventas_por_dia_semana' => $ventasPorDiaSemana,
                'ultimas_ventas' => $ultimasVentas,
                'alertas' => $alertas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}