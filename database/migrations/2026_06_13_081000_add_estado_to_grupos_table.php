<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            if (!Schema::hasColumn('grupos', 'estado')) {
                $table->string('estado', 20)->default('ACTIVO')->after('total_inscritos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            if (Schema::hasColumn('grupos', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};