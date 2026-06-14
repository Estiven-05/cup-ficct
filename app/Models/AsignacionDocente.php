<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionDocente extends Model
{
    protected $table = 'asignaciones_docentes';

    protected $fillable = [
        'docente_id',
        'grupo_id',
        'materia',
        'estado',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioClase::class, 'asignacion_docente_id');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaDocente::class, 'asignacion_docente_id');
    }
}
