<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-ventas', only: ['index', 'show', 'pdf']),
            new Middleware('permission:crear-ventas', only: ['create', 'store']),
            new Middleware('permission:eliminar-ventas', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $sales = Sale::query()
            ->with(['client', 'user'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->string('search').'%';
                // Se agrupa el OR entre paréntesis para no anular los filtros de fecha
                $q->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', $search)
                        ->orWhereHas('client', fn ($q) => $q->where('name', 'like', $search));
                });
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('sale_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('sale_date', '<=', $request->input('to')))
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'totalMonth' => Sale::whereMonth('sale_date', now()->month)
                ->whereYear('sale_date', now()->year)
                ->sum('total'),
        ]);
    }

    public function create()
    {
        return view('sales.create', [
            'clients' => Client::where('active', true)->orderBy('name')->get(['id', 'name', 'document_number']),
            'products' => Product::where('stock', '>', 0)->orderBy('nombre')->get(['id', 'nombre', 'precio', 'stock']),
        ]);
    }

    public function store(StoreSaleRequest $request, SaleService $service)
    {
        try {
            $sale = $service->register($request->validated(), $request->user()->id);

            return redirect()->route('sales.show', $sale)
                ->with('success', "Venta «{$sale->invoice_number}» registrada correctamente.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['client', 'user', 'details.product']);

        return view('sales.show', ['sale' => $sale]);
    }

    // Regla de negocio: al anular, se devuelve el stock (la lógica está en SaleService::cancel)
    public function destroy(Sale $sale, SaleService $service)
    {
        try {
            $service->cancel($sale);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('sales.index')
            ->with('success', "Venta «{$sale->invoice_number}» anulada y stock devuelto.");
    }

    // Clase 10: factura de venta en PDF
    public function pdf(Sale $sale)
    {
        $sale->load(['client', 'user', 'details.product']);

        return Pdf::loadView('pdf.sale', ['sale' => $sale])
            ->setPaper('letter')
            ->stream("{$sale->invoice_number}.pdf");
    }
}