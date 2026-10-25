<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
class ProductController extends Controller implements HasMiddleware
{
 // Permiso de Spatie que exige cada acción
 public static function middleware(): array
 {
 return [
 new Middleware('permission:ver-productos', only: ['index']),
 new Middleware('permission:crear-productos', only: ['create', 'store']),
 new Middleware('permission:editar-productos', only: ['edit', 'update']),
 new Middleware('permission:eliminar-productos', only: ['destroy', 'restore']),
 ];
 }
 public function index(Request $request)
 {
 // La papelera solo la ve quien puede eliminar
 $showTrashed = $request->boolean('trashed') && $request->user()->can('eliminar-productos');
 $products = Product::query()
 ->with(['category', 'proveedor'])
 ->when($showTrashed, fn ($query) => $query->onlyTrashed())
 ->when($request->filled('search'), function ($query) use ($request) {
 $query->where(function ($query) use ($request) {
$query->where('nombre', 'like', '%'.$request->string('search').'%')
 ->orWhere('descripcion', 'like', '%'.$request->string('search').'%');
 });
 })
 ->when($request->filled('category'), fn ($query) => $query->where('categoria_id', $request->integer('category')))
 ->latest($showTrashed ? 'deleted_at' : 'created_at')
 ->paginate(10)
 ->withQueryString();
 return view('products.index', [
 'products' => $products,
 'categories' => Category::orderBy('nombre')->get(['id', 'nombre']),
 'showTrashed' => $showTrashed,
 'trashedCount' => Product::onlyTrashed()->count(),
 ]);
 }
 public function create()
 {
 return view('products.create', [
 'product' => new Product(['stock' => 0]),
 'categories' => $this->selectableCategories(),
 'proveedores' => Provider::orderBy('name')->get(),
 ]);
 }
 public function store(StoreProductRequest $request)
 {
 Product::create($request->validated());
 return redirect()->route('products.index')
 ->with('success', 'Producto creado exitosamente.');
 }
 public function edit(Product $product)
 {
 return view('products.edit', [
 'product' => $product,
 'categories' => $this->selectableCategories(),
 'proveedores' => Provider::orderBy('name')->get(),
 ]);
 }
 public function update(UpdateProductRequest $request, Product $product)
 {
 $product->update($request->validated());
 return redirect()->route('products.index')
 ->with('success', 'Producto actualizado exitosamente.');
 }
 // Soft delete: el producto va a la papelera (se llena deleted_at)
 public function destroy(Product $product)
 {
 $product->delete();
 return redirect()->route('products.index')
 ->with('success', "Producto «{$product->nombre}» enviado a la papelera.");
 }
 public function restore(Product $product)
 {
 if ($product->category?->trashed()) {
 return back()->with('error', "Restaura primero la categoría «{$product->category->nombre}».");
 }
 $product->restore();
 return redirect()->route('products.index', ['trashed' => 1])
 ->with('success', "Producto «{$product->nombre}» restaurado.");
 }
 // Solo las categorías activas se pueden asignar a un producto
 private function selectableCategories()
 {
 return Category::orderBy('nombre')->get(['id', 'nombre']);
 }
}