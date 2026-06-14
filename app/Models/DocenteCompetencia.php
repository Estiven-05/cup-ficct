<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocenteCompetencia extends Model
{
    protected $table = 'docente_competencias';

    protected $fillable = [
        'docente_id',
        'materia',
        'tipo_respaldo',
        'descripcion',
        'archivo_respaldo',
        'estado',
        'observacion',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }
}
