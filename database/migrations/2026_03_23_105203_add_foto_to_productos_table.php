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

        Schema::table('productos', function (Blueprint $table) {

            // Añadimos la columna foto después de id_proveedor

            $table->string('foto')->nullable()->after('id_proveedor');

        });

    }


    /**

     * Reverse the migrations.

     */

    public function down(): void

    {

        Schema::table('productos', function (Blueprint $table) {

            // Eliminamos la columna si revertimos la migración

            $table->dropColumn('foto');

        });

    }

};
