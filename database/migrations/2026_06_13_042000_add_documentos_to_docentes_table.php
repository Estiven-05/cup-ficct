<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            foreach ([
                'archivo_titulo_profesional',
                'archivo_curriculum',
                'archivo_experiencia_docente',
                'archivo_certificado_capacitacion',
                'archivo_certificado_idioma',
                'archivo_otro_respaldo',
            ] as $column) {
                if (!Schema::hasColumn('docentes', $column)) {
                    $table->string($column)->nullable();
                }
            }

            if (!Schema::hasColumn('docentes', 'estado_documentos_docente')) {
                $table->string('estado_documentos_docente')->default('PENDIENTE');
            }

            if (!Schema::hasColumn('docentes', 'observacion_documentos_docente')) {
                $table->text('observacion_documentos_docente')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            foreach ([
                'archivo_titulo_profesional',
                'archivo_curriculum',
                'archivo_experiencia_docente',
                'archivo_certificado_capacitacion',
                'archivo_certificado_idioma',
                'archivo_otro_respaldo',
                'estado_documentos_docente',
                'observacion_documentos_docente',
            ] as $column) {
                if (Schema::hasColumn('docentes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
