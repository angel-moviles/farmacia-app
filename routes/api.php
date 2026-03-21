<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\LaboratorioController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\TipoProductoController;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VentaDetalleController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ImageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- AUTENTICACIÓN ---
Route::post('/login', [AuthController::class, 'login']);

// --- PRODUCTOS (Orden Crítico) ---
// La ruta de búsqueda DEBE ir antes del apiResource
Route::get('/productos/buscar/codigo/{codigo}', [ProductoController::class, 'buscarPorCodigoBarras']);
Route::apiResource('productos', ProductoController::class);

// --- RECURSOS GENERALES ---
Route::apiResource('roles', RolController::class);
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('proveedores', ProveedorController::class);
Route::apiResource('laboratorios', LaboratorioController::class);
Route::apiResource('tipoproductos', TipoProductoController::class);
Route::apiResource('presentaciones', PresentacionController::class);

// --- VENTAS ---
Route::get('/ventas', [VentaController::class, 'index']);
Route::post('/ventas', [VentaController::class, 'store']);
Route::get('/ventas/{id}', [VentaController::class, 'show']);
Route::apiResource('venta_detalles', VentaDetalleController::class);

// --- DASHBOARD ---
Route::get('/dashboard', [DashboardController::class, 'index']);

// --- IMÁGENES ---
Route::get('/images/usuarios/{filename}', [ImageController::class, 'getUsuarioImage']);

// --- REPORTES ---
Route::prefix('reportes')->group(function () {
    Route::get('/ventas', [ReporteController::class, 'ventasPorPeriodo']);
    Route::get('/productos-mas-vendidos', [ReporteController::class, 'productosMasVendidos']);
    Route::get('/inventario', [ReporteController::class, 'inventario']);
    Route::get('/ventas-por-usuario', [ReporteController::class, 'ventasPorUsuario']);
    Route::get('/movimientos', [ReporteController::class, 'movimientosInventario']);
});