<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_clases', function (Blueprint $table) {
            $table->id();

            // Relación con la asignación: docente + grupo + materia
            $table->foreignId('asignacion_docente_id')
                ->constrained('asignaciones_docentes')
                ->onDelete('cascade');

            // Aula donde se dictará la clase
            $table->foreignId('aula_id')
                ->constrained('aulas')
                ->onDelete('cascade');

            // Día y horario de clase
            $table->string('dia'); // Lunes, Martes, Miércoles, Jueves, Viernes, Sábado
            $table->time('hora_inicio');
            $table->time('hora_fin');

            // Estado del horario
            $table->string('estado')->default('ACTIVO');

            $table->timestamps();

            // Evita repetir exactamente el mismo horario para la misma asignación
            $table->unique(
                ['asignacion_docente_id', 'dia', 'hora_inicio', 'hora_fin'],
                'unique_asignacion_horario'
            );

            // Evita que la misma aula tenga dos clases exactamente en el mismo horario
            $table->unique(
                ['aula_id', 'dia', 'hora_inicio', 'hora_fin'],
                'unique_aula_horario'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_clases');
    }
};
