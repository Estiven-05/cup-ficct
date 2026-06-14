<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            if (!Schema::hasColumn('postulantes', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('apellidos');
            }
            if (!Schema::hasColumn('postulantes', 'sexo')) {
                $table->string('sexo', 20)->nullable()->after('fecha_nacimiento');
            }
            if (!Schema::hasColumn('postulantes', 'direccion')) {
                $table->string('direccion')->nullable()->after('sexo');
            }
            if (!Schema::hasColumn('postulantes', 'telefono')) {
                $table->string('telefono', 30)->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('postulantes', 'ciudad')) {
                $table->string('ciudad', 120)->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('postulantes', 'observacion_postulante')) {
                $table->text('observacion_postulante')->nullable()->after('colegio_procedencia');
            }
            if (!Schema::hasColumn('postulantes', 'fecha_pago')) {
                $table->timestamp('fecha_pago')->nullable()->after('monto_pago');
            }
            if (!Schema::hasColumn('postulantes', 'estado_registro')) {
                $table->string('estado_registro', 20)->default('ACTIVO')->after('estado_inscripcion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            foreach ([
                'fecha_nacimiento',
                'sexo',
                'direccion',
                'telefono',
                'ciudad',
                'observacion_postulante',
                'fecha_pago',
                'estado_registro',
            ] as $column) {
                if (Schema::hasColumn('postulantes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};