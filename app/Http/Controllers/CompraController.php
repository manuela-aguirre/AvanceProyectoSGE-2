<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $compras = Compra::with(['proveedor', 'producto'])
            ->buscar($request->get('q'))
            ->latest('fecha')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('compras.index', [
            'compras' => $compras,
            'q' => $request->get('q'),
        ]);
    }

    public function create()
    {
        return view('compras.create', [
            'productos' => Product::with('proveedor')->orderBy('stock')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'producto_id' => ['required', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'costo_unitario' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'in:pendiente,recibida'],
        ]);

        DB::transaction(function () use ($data) {
            $producto = Product::lockForUpdate()->findOrFail($data['producto_id']);
            $recibida = $data['estado'] === 'recibida';

            $compra = Compra::create($data + [
                'proveedor_id' => $producto->proveedor_id,
                'total' => $data['cantidad'] * $data['costo_unitario'],
                'fecha_recepcion' => $recibida ? now()->toDateString() : null,
            ]);
            $compra->update(['codigo' => sprintf('COM-%04d', $compra->id)]);

            if ($recibida) {
                $producto->increment('stock', $data['cantidad']);
            }
        });

        return redirect()->route('compras.index')->with('status', 'Compra registrada correctamente.');
    }

    public function recibir(Compra $compra)
    {
        if ($compra->estado === 'recibida') {
            return redirect()->route('compras.index')
                ->with('error', "La compra {$compra->codigo} ya había sido recibida.");
        }

        DB::transaction(function () use ($compra) {
            $producto = Product::lockForUpdate()->findOrFail($compra->producto_id);
            $producto->increment('stock', $compra->cantidad);
            $compra->update([
                'estado' => 'recibida',
                'fecha_recepcion' => now()->toDateString(),
            ]);
        });

        return redirect()->route('compras.index')
            ->with('status', "Compra {$compra->codigo} recibida: el stock aumentó en {$compra->cantidad} unidades.");
    }

    public function destroy(Compra $compra)
    {
        $error = DB::transaction(function () use ($compra) {
            if ($compra->estado === 'recibida') {
                $producto = Product::lockForUpdate()->findOrFail($compra->producto_id);

                if ($producto->stock < $compra->cantidad) {
                    return "No se puede anular: el stock actual ({$producto->stock}) es menor que lo recibido ({$compra->cantidad}); ya se vendió parte.";
                }

                $producto->decrement('stock', $compra->cantidad);
            }

            $compra->delete();

            return null;
        });

        if ($error) {
            return redirect()->route('compras.index')->with('error', $error);
        }

        return redirect()->route('compras.index')->with('status', 'Compra anulada y stock ajustado.');
    }
}
