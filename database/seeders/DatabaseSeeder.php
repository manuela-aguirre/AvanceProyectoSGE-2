<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'tipo_identificacion' => 'CC',
            'numero_identificacion' => '1234567890',
        ]);
        $user->assignRole('admin');

        // Ordena los datos base antes de crear ventas vinculadas.
        $this->call([
            ProviderSeeder::class,
            CategoriaSeeder::class,
            ProductSeeder::class,
            VentaSeeder::class,
            CompraSeeder::class,
            RolePermissionSeeder::class,
            ClientSeeder::class,
            TestUserSeeder::class,
            SaleSeeder::class,
        ]);
    }
}
