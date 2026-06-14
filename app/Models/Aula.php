<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    protected $table = 'aulas';

    protected $fillable = [
        'codigo',
        'pabellon',
        'capacidad',
        'estado',
    ];

    public function horarios()
    {
        return $this->hasMany(HorarioClase::class, 'aula_id');
    }
}