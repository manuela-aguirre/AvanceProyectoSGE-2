<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;
Route::view('/', 'welcome')->name('welcome');
Route::get('/dashboard', DashboardController::class)
 ->middleware(['auth', 'verified'])
 ->name('dashboard');
Route::middleware('auth')->group(function () {
 Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
 Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
 Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
 Route::get('/usuarios', [DashboardController::class, 'usuarios'])
 ->middleware('verified')
 ->name('usuarios.index');
 Route::resource('proveedores', ProveedorController::class)
 ->parameters(['proveedores' => 'proveedor'])
 ->except('show');
 Route::get('/ventas', fn () => redirect()->route('sales.index'))->name('ventas.index');
 Route::resource('compras', CompraController::class)
 ->only(['index', 'create', 'store', 'destroy']);
 Route::patch('compras/{compra}/recibir', [CompraController::class, 'recibir'])->name('compras.recibir');
 Route::patch('products/{product}/restore', [ProductController::class, 'restore'])
 ->withTrashed()
 ->name('products.restore');
 Route::resource('products', ProductController::class)->except('show');
 Route::patch('categories/{category}/restore', [CategoryController::class, 'restore'])
 ->withTrashed()
 ->name('categories.restore');
 Route::resource('categories', CategoryController::class)->except('show');
 Route::patch('clients/{client}/restore', [ClientController::class, 'restore'])
 ->withTrashed()
 ->name('clients.restore');
 Route::resource('clients', ClientController::class)->except('show');
 Route::resource('sales', SaleController::class)
 ->only(['index', 'create', 'store', 'show', 'destroy']);
});
require __DIR__.'/auth.php';
