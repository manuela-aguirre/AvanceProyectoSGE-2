<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'nombre', 'descripcion', 'imagen_url', 'stock', 'precio', 'codigo', 'proveedor_id', 'categoria_id',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
        ];
    }

    // Relación: Producto pertenece a una Proveedor
    public function proveedor()
{
    return $this->belongsTo(Provider::class, 'proveedor_id')->withDefault([
        'name' => 'Sin proveedor',
    ]);
}

    // Relación: Producto pertenece a un Categoría
    public function category()
    {
        $relacion = $this->belongsTo(Category::class, 'categoria_id')->withDefault([
            'nombre' => 'Sin categoría',
        ]);

        $relacion->withTrashed();

        return $relacion;
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

        // Un producto tiene muchos lotes
    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    /**
     * Scope reutilizable: filtra productos por texto (título o Código).
    * Uso: Product::buscar($texto)->get();
     */
    public function scopeBuscar($query, ?string $texto)
    {
        if (! $texto) {
            return $query;
        }

        return $query->where(function ($q) use ($texto) {
            $q->where('nombre', 'like', "%{$texto}%")
                ->orWhere('codigo', 'like', "%{$texto}%");
        });
    }

    /**
     * Scope reutilizable: solo productos con stock disponible.
    * Uso: Product::conStock()->get();
     */
    public function scopeConStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
