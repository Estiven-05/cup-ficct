<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            if (!Schema::hasColumn('grupos', 'turno')) {
                $table->string('turno')->default('MAÑANA')->after('nombre_grupo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            if (Schema::hasColumn('grupos', 'turno')) {
                $table->dropColumn('turno');
            }
        });
    }
};
