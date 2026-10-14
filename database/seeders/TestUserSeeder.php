<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
class TestUserSeeder extends Seeder
{
 /**
 * Usuarios de prueba (contraseña: password), uno por rol.
 */
 public function run(): void
 {
 $users = [
 'admin' => ['name' => 'Administrador', 'email' => 'admin@test.com'],
 'vendedor' => ['name' => 'Vendedor', 'email' => 'vendedor@test.com'],
 'almacenista' => ['name' => 'Almacenista', 'email' => 'almacenista@test.com'],
 ];
 foreach ($users as $role => $data) {
 $user = User::firstOrCreate(['email' => $data['email']], [
 'name' => $data['name'],
 'password' => 'password',
 ]);
 // La ruta /dashboard exige el middleware "verified"
 $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();
 $user->syncRoles([$role]);
 }
 }
}