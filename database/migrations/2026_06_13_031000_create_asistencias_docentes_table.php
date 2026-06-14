<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias_docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->foreignId('asignacion_docente_id')->constrained('asignaciones_docentes')->onDelete('cascade');
            $table->date('fecha');
            $table->string('estado');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->unique(['docente_id', 'asignacion_docente_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_docentes');
    }
};
