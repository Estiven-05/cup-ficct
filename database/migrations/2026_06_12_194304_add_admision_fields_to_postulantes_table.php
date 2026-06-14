<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->foreignId('carrera_asignada_id')
                ->nullable()
                ->after('carrera_2')
                ->constrained('carreras')
                ->nullOnDelete();

            $table->string('estado_admision')
                ->default('PENDIENTE')
                ->after('estado_inscripcion');

            $table->string('tipo_asignacion')
                ->nullable()
                ->after('estado_admision');

            $table->text('observacion_admision')
                ->nullable()
                ->after('tipo_asignacion');
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('carrera_asignada_id');
            $table->dropColumn('estado_admision');
            $table->dropColumn('tipo_asignacion');
            $table->dropColumn('observacion_admision');
        });
    }
};