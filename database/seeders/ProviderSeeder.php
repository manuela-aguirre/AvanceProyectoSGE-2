<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['document_type' => 'NIT', 'document_number' => '900111222', 'name' => 'Mayorista Digital SAS', 'contact_name' => 'Laura Ríos', 'email' => 'ventas@mayoristadigital.com', 'phone' => '6012345678', 'address' => 'Bogotá, Calle 80 #45-12'],
            ['document_type' => 'NIT', 'document_number' => '900333444', 'name' => 'Importadora Tech Andina', 'contact_name' => 'Pedro Salazar', 'email' => 'pedidos@techandina.com', 'phone' => '6044567890', 'address' => 'Medellín, Carrera 43A #1-50'],
            ['document_type' => 'NIT', 'document_number' => '900555666', 'name' => 'Accesorios del Pacífico', 'contact_name' => 'Diana Mejía', 'email' => 'comercial@accpacifico.com', 'phone' => '6027654321', 'address' => 'Cali, Av. 6N #23-45'],
            ['document_type' => 'CC', 'document_number' => '1094555666', 'name' => 'Andrés Cardona (independiente)', 'contact_name' => null, 'email' => 'andres.cardona@test.com', 'phone' => '3135556677', 'address' => 'Cartago, Calle 12 #5-30'],
            ['document_type' => 'NIT', 'document_number' => '900777001', 'name' => 'Tecnoparts S.A.S.', 'contact_name' => 'Sergio Vélez', 'email' => 'ventas@tecnoparts.com', 'phone' => '6011110001', 'address' => 'Bogotá, Cra 15 #80-20'],
            ['document_type' => 'NIT', 'document_number' => '900777002', 'name' => 'Papelería Mayorista', 'contact_name' => 'Rosa Gil', 'email' => 'pedidos@papmayorista.com', 'phone' => '6011110002', 'address' => 'Bogotá, Calle 13 #30-10'],
            ['document_type' => 'NIT', 'document_number' => '900777003', 'name' => 'Químicos y Aseo Ltda.', 'contact_name' => 'Juan Mora', 'email' => 'info@quimicosaseo.com', 'phone' => '6041110003', 'address' => 'Medellín, Cra 50 #20-15'],
            ['document_type' => 'NIT', 'document_number' => '900777004', 'name' => 'Alimentos del Campo', 'contact_name' => 'Marta Ríos', 'email' => 'ventas@alimentoscampo.com', 'phone' => '6021110004', 'address' => 'Cali, Calle 5 #40-22'],
            ['document_type' => 'NIT', 'document_number' => '900777005', 'name' => 'Ferretería Central', 'contact_name' => 'Óscar Peña', 'email' => 'compras@ferrecentral.com', 'phone' => '6061110005', 'address' => 'Pereira, Av. 30 de Agosto #25-10'],
            ['document_type' => 'NIT', 'document_number' => '900777006', 'name' => 'Comercial del Valle', 'contact_name' => 'Paula Soto', 'email' => 'ventas@comvalle.com', 'phone' => '6021110006', 'address' => 'Cartago, Calle 10 #8-45'],
            ['document_type' => 'NIT', 'document_number' => '900777007', 'name' => 'Importaciones Pacífico', 'contact_name' => 'Hugo Lara', 'email' => 'info@imppacifico.com', 'phone' => '6021110007', 'address' => 'Buenaventura, Cra 3 #2-10'],
            ['document_type' => 'NIT', 'document_number' => '900777008', 'name' => 'Distribuidora Andina', 'contact_name' => 'Elena Cruz', 'email' => 'pedidos@distandina.com', 'phone' => '6011110008', 'address' => 'Bogotá, Cra 7 #45-33'],
        ];

        // firstOrCreate: si el documento ya existe no lo duplica (se puede ejecutar varias veces)
        foreach ($providers as $provider) {
            Provider::firstOrCreate(['document_number' => $provider['document_number']], $provider);
        }
    }
}