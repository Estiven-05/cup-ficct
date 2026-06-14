<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            foreach ([
                'archivo_fotocopia_ci',
                'archivo_titulo_bachiller',
                'archivo_certificado_nacimiento',
                'archivo_fotografia',
                'archivo_formulario_inscripcion',
            ] as $column) {
                if (!Schema::hasColumn('postulantes', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            foreach ([
                'archivo_fotocopia_ci',
                'archivo_titulo_bachiller',
                'archivo_certificado_nacimiento',
                'archivo_fotografia',
                'archivo_formulario_inscripcion',
            ] as $column) {
                if (Schema::hasColumn('postulantes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
