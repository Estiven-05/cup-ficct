<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->boolean('doc_fotocopia_ci')->default(false)->after('titulo_bachiller');
            $table->boolean('doc_titulo_bachiller')->default(false)->after('doc_fotocopia_ci');
            $table->boolean('doc_certificado_nacimiento')->default(false)->after('doc_titulo_bachiller');
            $table->boolean('doc_fotografia')->default(false)->after('doc_certificado_nacimiento');
            $table->boolean('doc_formulario_inscripcion')->default(false)->after('doc_fotografia');

            $table->string('metodo_pago')->nullable()->after('estado_pago');
            $table->string('codigo_transaccion')->nullable()->after('metodo_pago');
            $table->decimal('monto_pago', 10, 2)->nullable()->after('codigo_transaccion');
            $table->string('estado_pago_revision')->default('PENDIENTE')->after('monto_pago');

            $table->string('estado_requisitos')->default('PENDIENTE')->after('estado_pago_revision');
            $table->text('observacion_requisitos')->nullable()->after('estado_requisitos');

            $table->timestamp('fecha_envio_requisitos')->nullable()->after('observacion_requisitos');
            $table->timestamp('fecha_validacion_requisitos')->nullable()->after('fecha_envio_requisitos');
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropColumn([
                'doc_fotocopia_ci',
                'doc_titulo_bachiller',
                'doc_certificado_nacimiento',
                'doc_fotografia',
                'doc_formulario_inscripcion',
                'metodo_pago',
                'codigo_transaccion',
                'monto_pago',
                'estado_pago_revision',
                'estado_requisitos',
                'observacion_requisitos',
                'fecha_envio_requisitos',
                'fecha_validacion_requisitos',
            ]);
        });
    }
};