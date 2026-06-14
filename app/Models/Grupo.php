<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    // Le aclaramos a Laravel el nombre exacto de la tabla en pgAdmin
    protected $table = 'grupos';

    // Campos que permitimos llenar desde el código
    protected $fillable = ['nombre_grupo', 'turno', 'cupo_maximo', 'total_inscritos', 'estado'];

    // Relación: Un grupo tiene muchos postulantes
    public function postulantes()
    {
        return $this->hasMany(Postulante::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionDocente::class, 'grupo_id');
    }
}
