<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            // Clave primaria personalizada
            $table->id('id_producto');

            // Identificadores únicos
            $table->string('lote')->unique();
            // Agregamos codigo_barras (String para soportar ceros a la izquierda)
            $table->string('codigo_barras', 50)->nullable()->unique();
            
            $table->string('nombre');

            // Fechas de control
            $table->date('fecha_produccion');
            $table->date('fecha_caducidad');

            // Valores monetarios (10 enteros, 2 decimales)
            $table->decimal('costo', 10, 2);
            $table->decimal('precio_venta', 10, 2);

            // Inventario
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(0);

            // Estado del producto
            $table->boolean('activo')->default(true);

            // Relaciones (Foreign Keys)
            // Nota: Asegúrate de que los nombres de las tablas coincidan con tus otras migraciones
            $table->foreignId('id_laboratorio')->constrained('laboratorios', 'id_laboratorio');
            $table->foreignId('id_tipo_producto')->constrained('tipo_productos', 'id_tipo_producto');
            $table->foreignId('id_presentacion')->constrained('presentacions', 'id_presentacion');
            $table->foreignId('id_proveedor')->constrained('proveedors', 'id_proveedor');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};