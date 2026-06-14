<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();

            // Datos físicos del aula
            $table->string('codigo')->unique(); // Ej: Aula 101, Lab 3, Auditorio A
            $table->string('pabellon')->nullable(); // Ej: Pabellón A, Módulo 236
            $table->integer('capacidad');

            // Estado del aula
            $table->string('estado')->default('DISPONIBLE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};