<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreProductRequest extends FormRequest
{
 // Los permisos se validan en el controlador (middleware de Spatie)
 public function authorize(): bool
 {
 return true;
 }
 public function rules(): array
 {
 return [
 'nombre' => ['required', 'string', 'max:150'],
 'descripcion' => ['nullable', 'string'],
 'imagen_url' => ['nullable', 'url', 'max:255'],
 'stock' => ['required', 'integer', 'min:0'],
 'precio' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
 'codigo' => ['nullable', 'string', 'max:20'],
 'proveedor_id' => ['required', 'exists:proveedores,id'],
 'categoria_id' => ['required', 'exists:categorias,id'],
 ];
 }
 public function attributes(): array
 {
 return [
 'nombre' => 'nombre',
 'descripcion' => 'descripción',
 'imagen_url' => 'imagen',
 'precio' => 'precio',
 'proveedor_id' => 'proveedor',
 'categoria_id' => 'categoría',
 ];
 }
}
