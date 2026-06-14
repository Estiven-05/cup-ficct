<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_docentes', function (Blueprint $table) {
            $table->id();

            // Docente asignado
            $table->foreignId('docente_id')
                ->constrained('docentes')
                ->onDelete('cascade');

            // Grupo al que se asigna
            $table->foreignId('grupo_id')
                ->constrained('grupos')
                ->onDelete('cascade');

            // Materia que dictará el docente en ese grupo
            $table->string('materia');

            // Estado de la asignación
            $table->string('estado')->default('ACTIVA');

            $table->timestamps();

            // Evita repetir el mismo docente en la misma materia y grupo
            $table->unique(['docente_id', 'grupo_id', 'materia']);

            // Evita que una misma materia del mismo grupo tenga dos docentes
            $table->unique(['grupo_id', 'materia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_docentes');
    }
};