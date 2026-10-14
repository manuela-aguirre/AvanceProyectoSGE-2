<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = Proveedor::pluck('id', 'nombre');
        $categorias = Category::pluck('id', 'nombre');

        $productos = [
            ['nombre' => 'Mouse inalámbrico', 'descripcion' => 'Mouse óptico 2.4 GHz', 'stock' => 25, 'codigo' => 'PRD-0001', 'proveedor' => 'Tecnoparts S.A.S.', 'categoria' => 'Tecnología'],
            ['nombre' => 'Teclado mecánico', 'descripcion' => 'Teclado USB con switches azules', 'stock' => 12, 'codigo' => 'PRD-0002', 'proveedor' => 'Tecnoparts S.A.S.', 'categoria' => 'Tecnología'],
            ['nombre' => 'Resma de papel carta', 'descripcion' => '500 hojas, 75 g', 'stock' => 40, 'codigo' => 'PRD-0003', 'proveedor' => 'Papelería Mayorista', 'categoria' => 'Papelería'],
            ['nombre' => 'Cuaderno argollado 100 hojas', 'descripcion' => 'Cuadriculado, tapa dura', 'stock' => 60, 'codigo' => 'PRD-0004', 'proveedor' => 'Papelería Mayorista', 'categoria' => 'Papelería'],
            ['nombre' => 'Detergente en polvo 1 kg', 'descripcion' => 'Para ropa de color y blanca', 'stock' => 18, 'codigo' => 'PRD-0005', 'proveedor' => 'Químicos y Aseo Ltda.', 'categoria' => 'Aseo'],
            ['nombre' => 'Desinfectante multiusos 1 L', 'descripcion' => 'Aroma lavanda', 'stock' => 2, 'codigo' => 'PRD-0006', 'proveedor' => 'Químicos y Aseo Ltda.', 'categoria' => 'Aseo'],
            ['nombre' => 'Arroz premium 500 g', 'descripcion' => 'Grano largo', 'stock' => 80, 'codigo' => 'PRD-0007', 'proveedor' => 'Alimentos del Campo', 'categoria' => 'Alimentos'],
            ['nombre' => 'Aceite de cocina 1 L', 'descripcion' => 'Aceite vegetal', 'stock' => 1, 'codigo' => 'PRD-0008', 'proveedor' => 'Alimentos del Campo', 'categoria' => 'Alimentos'],
            ['nombre' => 'Taladro inalámbrico 12 V', 'descripcion' => 'Incluye batería y cargador', 'stock' => 7, 'codigo' => 'PRD-0009', 'proveedor' => 'Ferretería Central', 'categoria' => 'Herramientas'],
            ['nombre' => 'Juego de destornilladores', 'descripcion' => 'Set de 12 piezas', 'stock' => 15, 'codigo' => 'PRD-0010', 'proveedor' => 'Ferretería Central', 'categoria' => 'Ferretería'],
            ['nombre' => 'Silla ergonómica de oficina', 'descripcion' => 'Con soporte lumbar y ruedas', 'stock' => 5, 'codigo' => 'PRD-0011', 'proveedor' => 'Comercial del Valle', 'categoria' => 'Oficina'],
            ['nombre' => 'Cable HDMI 2 m', 'descripcion' => 'Alta velocidad 4K', 'stock' => 30, 'codigo' => 'PRD-0012', 'proveedor' => 'Importaciones Pacífico', 'categoria' => 'Electrónica'],
            ['nombre' => 'Audífonos con micrófono', 'descripcion' => 'Diadema, conexión 3.5 mm', 'stock' => 9, 'codigo' => 'PRD-0013', 'proveedor' => 'Distribuidora Andina', 'categoria' => 'Accesorios'],
        ];

        DB::transaction(function () use ($productos, $proveedores, $categorias) {
            foreach ($productos as $producto) {
                $coincidentes = Product::withTrashed()
                    ->where('codigo', $producto['codigo'])
                    ->orderBy('id')
                    ->get();

                $principal = $coincidentes->first(fn (Product $item) => ! $item->trashed())
                    ?? $coincidentes->first();

                $datos = [
                    'nombre' => $producto['nombre'],
                    'descripcion' => $producto['descripcion'],
                    'precio' => random_int(5000, 500000),
                    'codigo' => $producto['codigo'],
                    'proveedor_id' => $proveedores[$producto['proveedor']],
                    'categoria_id' => $categorias[$producto['categoria']],
                ];

                if (! $principal) {
                    Product::create($datos + [
                        'stock' => $producto['stock'],
                    ]);

                    continue;
                }

                foreach ($coincidentes->where('id', '!=', $principal->id) as $duplicado) {
                    foreach (['compras' => 'producto_id', 'ventas' => 'producto_id', 'sale_details' => 'product_id'] as $tabla => $columna) {
                        if (Schema::hasTable($tabla)) {
                            DB::table($tabla)
                                ->where($columna, $duplicado->id)
                                ->update([$columna => $principal->id]);
                        }
                    }

                    $duplicado->forceDelete();
                }

                $principal->update($datos);
            }
        });
    }
}