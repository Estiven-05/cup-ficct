<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();

            // Relación opcional con la tabla users
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->string('ci')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('correo')->unique()->nullable();
            $table->string('telefono')->nullable();

            // Requisito principal del enunciado
            $table->string('profesion');

            // Requisitos académicos
            $table->boolean('es_profesional')->default(false);
            $table->boolean('tiene_maestria')->default(false);
            $table->boolean('tiene_diplomado')->default(false);

            // Estado del docente dentro del CUP
            $table->string('estado')->default('PENDIENTE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
