
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postulantes', function (Blueprint $table) {
            $table->id();
            $table->string('ci')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('carrera_1');
            $table->string('carrera_2');
            $table->string('colegio_procedencia');
            $table->boolean('titulo_bachiller')->default(false); // Requisito obligatorio
            $table->boolean('estado_pago')->default(false);      // Pago mediante pasarela simulada
            $table->string('estado_inscripcion')->default('pendiente'); // pendiente, aprobado, reprobado
            
            // Relación con la tabla grupos (Llave Foránea)
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulantes');
    }
};