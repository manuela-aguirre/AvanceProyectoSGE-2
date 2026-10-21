<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
class RolePermissionSeeder extends Seeder
{
 public function run(): void
 {
 // Limpiar la caché de permisos de Spatie
 app(PermissionRegistrar::class)->forgetCachedPermissions();
 $permissions = [
 'ver-productos', 'crear-productos', 'editar-productos', 'eliminar-productos',
 'ver-categorias', 'crear-categorias', 'editar-categorias', 'eliminar-categorias',
 'ver-clientes', 'crear-clientes', 'editar-clientes', 'eliminar-clientes',
 'ver-ventas', 'crear-ventas', 'editar-ventas', 'eliminar-ventas',
 // Proveedores (Clase 10)
 'ver-proveedores', 'crear-proveedores', 'editar-proveedores', 'eliminar-proveedores',
 // Compras (Clase 10: se agrega recibir-compras)
 'ver-compras', 'crear-compras', 'editar-compras', 'eliminar-compras', 'recibir-compras',
 'ver-reportes',
 // Clientes
 'ver-clientes', 'crear-clientes', 'editar-clientes', 'eliminar-clientes',
 // Ventas
 'ver-ventas', 'crear-ventas', 'editar-ventas', 'eliminar-ventas',
 // Reportes
 'ver-reportes'
 ];
 // findOrCreate: si el permiso ya existe no lo duplica
 foreach ($permissions as $permission) {
 Permission::findOrCreate($permission);
 }
 // syncPermissions: deja al rol exactamente con estos permisos
 Role::findOrCreate('admin')->syncPermissions(Permission::all());
 Role::findOrCreate('vendedor')->syncPermissions([
 'ver-productos',
 'ver-clientes', 'crear-clientes', 'editar-clientes',
 'ver-ventas', 'crear-ventas',
]);
Role::findOrCreate('almacenista')->syncPermissions([
 'ver-productos', 'crear-productos', 'editar-productos',
 'ver-categorias',
 'ver-proveedores', 'crear-proveedores', 'editar-proveedores',
 'ver-compras', 'crear-compras', 'recibir-compras',
]);

 }
}
