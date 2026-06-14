<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('notas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('postulante_id')->constrained('postulantes')->onDelete('cascade');
        
        // Exámenes por materia
        $table->integer('computacion_1')->default(0);
        $table->integer('computacion_2')->default(0);
        $table->integer('computacion_3')->default(0);

        $table->integer('matematicas_1')->default(0);
        $table->integer('matematicas_2')->default(0);
        $table->integer('matematicas_3')->default(0);

        $table->integer('ingles_1')->default(0);
        $table->integer('ingles_2')->default(0);
        $table->integer('ingles_3')->default(0);

        $table->integer('fisica_1')->default(0);
        $table->integer('fisica_2')->default(0);
        $table->integer('fisica_3')->default(0);

        $table->decimal('promedio', 5, 2)->default(0);
        $table->string('estado')->default('REPROBADO'); // APROBADO o REPROBADO
        $table->timestamps();

    });
}
};
