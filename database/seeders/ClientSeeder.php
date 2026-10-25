<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Clientes de prueba (10 registros).
     */
    public function run(): void
    {
        $clients = [
            ['document_type' => 'CC', 'document_number' => '1088123456', 'name' => 'Juan Pérez', 'email' => 'juan@test.com', 'phone' => '3001234567', 'address' => 'Calle 10 #20-30'],
            ['document_type' => 'NIT', 'document_number' => '900123456', 'name' => 'TecnoSolutions SAS', 'email' => 'contacto@tecno.com', 'phone' => '3109876543', 'address' => 'Carrera 5 #45-10'],
            ['document_type' => 'CC', 'document_number' => '1098765432', 'name' => 'María Gómez', 'email' => 'maria@test.com', 'phone' => '3201112233', 'address' => 'Av. Principal #12-34'],
            ['document_type' => 'CE', 'document_number' => 'E1234567', 'name' => 'Carlos Rodríguez', 'email' => 'carlos@test.com', 'phone' => '3154445566', 'address' => 'Transversal 8 #90-12'],
            ['document_type' => 'CC', 'document_number' => '1055443322', 'name' => 'Ana Martínez', 'email' => 'ana@test.com', 'phone' => '3187778899', 'address' => 'Diagonal 15 #40-50'],
            ['document_type' => 'CC', 'document_number' => '1020304050', 'name' => 'Luis Gómez', 'email' => 'luis@test.com', 'phone' => '3011110001', 'address' => 'Calle 1 #2-3'],
            ['document_type' => 'CC', 'document_number' => '1011223344', 'name' => 'Sofía Ramírez', 'email' => 'sofia@test.com', 'phone' => '3011110002', 'address' => 'Cra 4 #5-6'],
            ['document_type' => 'NIT', 'document_number' => '900888001', 'name' => 'Comercial Los Andes S.A.S.', 'email' => 'andes@test.com', 'phone' => '3011110003', 'address' => 'Av. 7 #8-9'],
            ['document_type' => 'CC', 'document_number' => '1033445566', 'name' => 'Pedro Castaño', 'email' => 'pedro@test.com', 'phone' => '3011110004', 'address' => 'Calle 20 #10-11'],
            ['document_type' => 'CC', 'document_number' => '1055667788', 'name' => 'Laura Ospina', 'email' => 'laura@test.com', 'phone' => '3011110005', 'address' => 'Cra 12 #13-14'],
        ];

        // firstOrCreate: si el documento ya existe no lo duplica (se puede ejecutar varias veces)
        foreach ($clients as $client) {
            Client::firstOrCreate(['document_number' => $client['document_number']], $client);
        }
    }
}