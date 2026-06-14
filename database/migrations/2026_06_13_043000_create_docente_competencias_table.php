<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->string('materia');
            $table->string('tipo_respaldo');
            $table->text('descripcion')->nullable();
            $table->string('archivo_respaldo')->nullable();
            $table->string('estado')->default('PENDIENTE');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['docente_id', 'materia', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_competencias');
    }
};
