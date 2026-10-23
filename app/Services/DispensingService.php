<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispensingService
{
    public function dispense(Product $product, int $quantity): array
    {
        return DB::transaction(function () use ($product, $quantity) {
            // Bloquea el producto mientras dura la transacción
            $product = Product::lockForUpdate()->findOrFail($product->id);

            // 1. Ignorar lotes vencidos y vacíos.  2. FEFO: el que vence antes va primero
            $lots = Lot::where('product_id', $product->id)
                ->where('quantity', '>', 0)
                ->where('expiration_date', '>=', today()->toDateString())
                ->orderBy('expiration_date')
                ->lockForUpdate()
                ->get();

            // 3. Validar que alcance el stock no vencido
            $available = (int) $lots->sum('quantity');

            if ($quantity < 1 || $available < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Stock insuficiente para «{$product->nombre}». Disponible sin vencer: {$available}, solicitado: {$quantity}.",
                ]);
            }

            // 4. Descontar primero del lote que vence antes
            $remaining = $quantity;
            $used = [];

            foreach ($lots as $lot) {
                if ($remaining === 0) {
                    break;
                }

                $take = min($lot->quantity, $remaining);
                $lot->decrement('quantity', $take);
                $remaining -= $take;
                $used[] = ['lote' => $lot->lot_number, 'cantidad' => $take];
            }

            // 5. Descontar también el stock total del producto
            $product->decrement('stock', $quantity);

            return $used;
        });
    }
}
