<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id('id_venta_detalle');

            $table->integer('cantidad');
            $table->decimal('precio_unitario',10,2);
            $table->decimal('subtotal',10,2);

            $table->foreignId('id_venta')
                ->constrained('ventas','id_venta')
                ->cascadeOnDelete();

            $table->foreignId('id_producto')
                ->constrained('productos','id_producto');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('venta_detalles');
    }
};