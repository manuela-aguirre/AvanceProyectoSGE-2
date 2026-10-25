<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('imagen_url', 255)->nullable();
           $table->integer('stock')->default(0);
$table->decimal('precio', 12, 2)->nullable();
$table->string('codigo', 20)->nullable();
$table->foreignId('proveedor_id')->constrained('providers')->restrictOnDelete();
$table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
