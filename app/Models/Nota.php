<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $fillable = [
        'postulante_id',
        'computacion_1',
        'computacion_2',
        'computacion_3',
        'matematicas_1',
        'matematicas_2',
        'matematicas_3',
        'ingles_1',
        'ingles_2',
        'ingles_3',
        'fisica_1',
        'fisica_2',
        'fisica_3',
        'promedio',
        'estado',
    ];
}