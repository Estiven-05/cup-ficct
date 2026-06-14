<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioClase extends Model
{
    protected $table = 'horarios_clases';

    protected $fillable = [
        'asignacion_docente_id',
        'aula_id',
        'dia',
        'hora_inicio',
        'hora_fin',
        'turno',
        'modalidad',
        'examen_presencial',
        'observacion_horario',
        'estado',
    ];

    protected $casts = [
        'examen_presencial' => 'boolean',
    ];

    public function asignacionDocente()
    {
        return $this->belongsTo(AsignacionDocente::class, 'asignacion_docente_id');
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }
}
