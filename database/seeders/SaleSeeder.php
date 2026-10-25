<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::pluck('id', 'document_number');
        $products = Product::pluck('id', 'codigo');
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $sales = [
            ['FV-00001', '1088123456', 'PRD-0001', 2, 65000, 30, 'pagada'],
            ['FV-00002', '900123456', 'PRD-0003', 10, 14000, 7, 'pagada'],
            ['FV-00003', '1098765432', 'PRD-0009', 1, 189000, 2, 'pendiente'],
            ['FV-00004', '1020304050', 'PRD-0004', 5, 6500, 14, 'pagada'],
            ['FV-00005', '1011223344', 'PRD-0012', 3, 22000, 10, 'pendiente'],
            ['FV-00006', '900888001', 'PRD-0005', 6, 18500, 7, 'pendiente'],
            ['FV-00007', '1033445566', 'PRD-0013', 2, 48000, 5, 'pagada'],
            ['FV-00008', '1055667788', 'PRD-0010', 4, 35000, 3, 'pagada'],
            ['FV-00009', '1088123456', 'PRD-0002', 1, 90000, 2, 'pagada'],
            ['FV-00010', '1098765432', 'PRD-0007', 12, 4200, 1, 'pagada'],
        ];

        foreach ($sales as [$invoice, $document, $productCode, $quantity, $unitPrice, $daysAgo, $status]) {
            $subtotal = $quantity * $unitPrice;
            $tax = round($subtotal * 0.19, 2);

            $sale = Sale::withTrashed()->firstOrCreate(
                ['invoice_number' => $invoice],
                [
                    'client_id' => $clients[$document],
                    'user_id' => $user->id,
                    'sale_date' => now()->subDays($daysAgo)->toDateString(),
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $subtotal + $tax,
                    'status' => $status,
                ],
            );

            if ($sale->trashed()) {
                $sale->restore();
            }

            if (! $sale->details()->exists()) {
                $sale->details()->create([
                    'product_id' => $products[$productCode],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }
        }
    }
}