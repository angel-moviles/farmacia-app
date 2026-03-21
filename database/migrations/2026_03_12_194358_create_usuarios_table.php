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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('clave_usuario')->unique();
            $table->string('nombre');
            $table->string('a_paterno');
            $table->string('a_materno')->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('sexo',['M','F']);
            $table->string('telefono')->nullable();
            $table->string('correo')->unique();
            $table->string('foto')->nullable();
            $table->string('contrasena');
            $table->boolean('activo')->default(true);

            $table->foreignId('id_rol')->constrained('rols','id_rol');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
