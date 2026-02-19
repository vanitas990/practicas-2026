<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VehicleController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (sin autenticación)
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'message' => 'API is running']);
});

// Grupo de rutas protegidas (comentar si no tienes autenticación configurada)
// Route::middleware('auth:sanctum')->group(function () {

// === PRODUCTOS ===
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/low-stock', [ProductController::class, 'lowStock']);
    Route::get('/stats', [ProductController::class, 'stats']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::post('/{id}/update-stock', [ProductController::class, 'updateStock']);
});

// === CLIENTES ===
Route::prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::get('/{id}', [CustomerController::class, 'show']);
    Route::put('/{id}', [CustomerController::class, 'update']);
    Route::delete('/{id}', [CustomerController::class, 'destroy']);
});

// === VENTAS ===
Route::prefix('sales')->group(function () {
    Route::get('/', [SaleController::class, 'index']);
    Route::post('/', [SaleController::class, 'store']);
    Route::get('/stats', [SaleController::class, 'stats']);
    Route::get('/{id}', [SaleController::class, 'show']);
    Route::post('/{id}/cancel', [SaleController::class, 'cancel']);
});

// === SERVICIOS ===
Route::prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::post('/', [ServiceController::class, 'store']);
    Route::get('/{id}', [ServiceController::class, 'show']);
    Route::put('/{id}', [ServiceController::class, 'update']);
    Route::delete('/{id}', [ServiceController::class, 'destroy']);
});

// === REGISTROS DE SERVICIOS ===
Route::prefix('service-records')->group(function () {
    Route::get('/', [ServiceController::class, 'records']);
    Route::post('/', [ServiceController::class, 'storeRecord']);
    Route::get('/{id}', [ServiceController::class, 'showRecord']);
});

// === GASTOS ===
Route::prefix('expenses')->group(function () {
    Route::get('/', [ExpenseController::class, 'index']);
    Route::post('/', [ExpenseController::class, 'store']);
    Route::get('/summary', [ExpenseController::class, 'summary']);
    Route::get('/categories', [ExpenseController::class, 'categories']);
    Route::get('/{id}', [ExpenseController::class, 'show']);
    Route::put('/{id}', [ExpenseController::class, 'update']);
    Route::delete('/{id}', [ExpenseController::class, 'destroy']);
});

// === TIPO DE CAMBIO ===
Route::prefix('exchange-rate')->group(function () {
    Route::get('/current', [ExchangeRateController::class, 'current']);
    Route::post('/update-api', [ExchangeRateController::class, 'updateFromApi']);
    Route::post('/update-manual', [ExchangeRateController::class, 'updateManually']);
    Route::get('/history', [ExchangeRateController::class, 'history']);
    Route::post('/convert', [ExchangeRateController::class, 'convert']);
});

// === REPORTES ===
Route::prefix('reports')->group(function () {
    Route::get('/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/financial', [ReportController::class, 'financial']);
    Route::get('/inventory', [ReportController::class, 'inventory']);
});
Route::prefix('vehicles')->group(function () {
    Route::get('/', [VehicleController::class, 'index']);
    Route::post('/', [VehicleController::class, 'store']);
    Route::get('/{id}', [VehicleController::class, 'show']);
});

// }); // Fin del grupo protegido

/*
|--------------------------------------------------------------------------
| Documentación de la API
|--------------------------------------------------------------------------
|
| === PRODUCTOS ===
| GET    /api/products                      - Listar productos
| POST   /api/products                      - Crear producto
| GET    /api/products/low-stock            - Productos con bajo stock
| GET    /api/products/stats                - Estadísticas de productos
| GET    /api/products/{id}                 - Ver producto
| PUT    /api/products/{id}                 - Actualizar producto
| DELETE /api/products/{id}                 - Eliminar producto
| POST   /api/products/{id}/update-stock    - Actualizar stock
|
| === CLIENTES ===
| GET    /api/customers                     - Listar clientes
| POST   /api/customers                     - Crear cliente
| GET    /api/customers/{id}                - Ver cliente
| PUT    /api/customers/{id}                - Actualizar cliente
| DELETE /api/customers/{id}                - Eliminar cliente
|
| === VENTAS ===
| GET    /api/sales                         - Listar ventas
| POST   /api/sales                         - Crear venta
| GET    /api/sales/stats                   - Estadísticas de ventas
| GET    /api/sales/{id}                    - Ver venta
| POST   /api/sales/{id}/cancel             - Cancelar venta
|
| === SERVICIOS ===
| GET    /api/services                      - Listar servicios
| POST   /api/services                      - Crear servicio
| GET    /api/services/{id}                 - Ver servicio
| PUT    /api/services/{id}                 - Actualizar servicio
| DELETE /api/services/{id}                 - Eliminar servicio
|
| === REGISTROS DE SERVICIOS ===
| GET    /api/service-records               - Listar registros
| POST   /api/service-records               - Crear registro
| GET    /api/service-records/{id}          - Ver registro
|
| === GASTOS ===
| GET    /api/expenses                      - Listar gastos
| POST   /api/expenses                      - Crear gasto
| GET    /api/expenses/summary              - Resumen de gastos
| GET    /api/expenses/categories           - Categorías disponibles
| GET    /api/expenses/{id}                 - Ver gasto
| PUT    /api/expenses/{id}                 - Actualizar gasto
| DELETE /api/expenses/{id}                 - Eliminar gasto
|
| === TIPO DE CAMBIO ===
| GET    /api/exchange-rate/current         - Tipo de cambio actual
| POST   /api/exchange-rate/update-api      - Actualizar desde API
| POST   /api/exchange-rate/update-manual   - Actualizar manualmente
| GET    /api/exchange-rate/history         - Historial
| POST   /api/exchange-rate/convert         - Convertir montos
|
| === REPORTES ===
| GET    /api/reports/dashboard             - Dashboard principal
| GET    /api/reports/financial             - Reporte financiero
| GET    /api/reports/inventory             - Reporte de inventario
|
*/