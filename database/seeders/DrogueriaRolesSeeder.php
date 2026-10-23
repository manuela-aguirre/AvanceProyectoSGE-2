<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DrogueriaRolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'ver-productos', 'crear-productos', 'editar-productos', 'eliminar-productos',
            'ver-lotes', 'editar-lotes', 'recibir-lotes',
            'ver-proveedores', 'crear-proveedores', 'editar-proveedores', 'eliminar-proveedores',
            'ver-clientes', 'crear-clientes', 'editar-clientes', 'eliminar-clientes',
            'ver-compras', 'crear-compras', 'editar-compras', 'eliminar-compras', 'recibir-compras',
            'ver-ventas', 'crear-ventas', 'editar-ventas', 'eliminar-ventas', 'dispensar-control-especial',
            'ver-reportes',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Admin: todo menos recibir mercancía y autorizar control especial
        $adminPermissions = Permission::all()->pluck('name')
            ->diff(['recibir-compras', 'recibir-lotes', 'dispensar-control-especial'])
            ->values()
            ->all();

        Role::findOrCreate('admin')->syncPermissions($adminPermissions);

        Role::findOrCreate('regente')->syncPermissions([
            'ver-productos', 'crear-productos', 'editar-productos',
            'ver-lotes', 'editar-lotes', 'recibir-lotes',
            'ver-proveedores',
            'ver-clientes', 'crear-clientes', 'editar-clientes',
            'ver-compras', 'recibir-compras',
            'ver-ventas', 'dispensar-control-especial',
            'ver-reportes',
        ]);

        Role::findOrCreate('cajero')->syncPermissions([
            'ver-productos',
            'ver-clientes', 'crear-clientes', 'editar-clientes',
            'ver-ventas', 'crear-ventas',
        ]);

        Role::findOrCreate('auxiliar-bodega')->syncPermissions([
            'ver-productos',
            'ver-lotes', 'recibir-lotes',
            'ver-proveedores',
            'ver-compras', 'recibir-compras',
        ]);
    }
}
