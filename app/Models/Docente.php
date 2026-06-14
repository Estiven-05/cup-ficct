<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docentes';

    protected $fillable = [
        'user_id',
        'ci',
        'nombres',
        'apellidos',
        'correo',
        'telefono',
        'profesion',
        'es_profesional',
        'tiene_maestria',
        'tiene_diplomado',
        'estado',
        'archivo_titulo_profesional',
        'archivo_curriculum',
        'archivo_experiencia_docente',
        'archivo_certificado_capacitacion',
        'archivo_certificado_idioma',
        'archivo_otro_respaldo',
        'estado_documentos_docente',
        'observacion_documentos_docente',
    ];

    protected $casts = [
        'es_profesional' => 'boolean',
        'tiene_maestria' => 'boolean',
        'tiene_diplomado' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionDocente::class, 'docente_id');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaDocente::class, 'docente_id');
    }

    public function competencias()
    {
        return $this->hasMany(DocenteCompetencia::class, 'docente_id');
    }
}
