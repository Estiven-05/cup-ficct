<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios_clases', function (Blueprint $table) {
            if (!Schema::hasColumn('horarios_clases', 'turno')) {
                $table->string('turno')->default('MAÑANA')->after('hora_fin');
            }

            if (!Schema::hasColumn('horarios_clases', 'modalidad')) {
                $table->string('modalidad')->default('PRESENCIAL')->after('turno');
            }

            if (!Schema::hasColumn('horarios_clases', 'examen_presencial')) {
                $table->boolean('examen_presencial')->default(true)->after('modalidad');
            }

            if (!Schema::hasColumn('horarios_clases', 'observacion_horario')) {
                $table->text('observacion_horario')->nullable()->after('examen_presencial');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE horarios_clases ALTER COLUMN aula_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('horarios_clases', function (Blueprint $table) {
            $table->dropColumn([
                'turno',
                'modalidad',
                'examen_presencial',
                'observacion_horario',
            ]);
        });
    }
};
