<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Carrera;

class Postulante extends Model
{
    // Nombre explícito de la tabla en tu PostgreSQL
    protected $table = 'postulantes';

    // Campos habilitados para asignación masiva (incluimos user_id para el futuro login)
    protected $fillable = [
        'user_id',
        'ci',
        'nombres',
        'apellidos',
        'fecha_pago',
        'estado_registro',
        'observacion_postulante',
        'ciudad',
        'telefono',
        'direccion',
        'sexo',
        'fecha_nacimiento',
        'carrera_1',
        'carrera_2',
        'carrera_asignada_id',
        'colegio_procedencia',
        'titulo_bachiller',
        'estado_pago',
        'estado_inscripcion',
        'estado_admision',
        'tipo_asignacion',
        'observacion_admision',
        'grupo_id',

        'doc_fotocopia_ci',
        'doc_titulo_bachiller',
        'doc_certificado_nacimiento',
        'doc_fotografia',
        'doc_formulario_inscripcion',
        'metodo_pago',
        'codigo_transaccion',
        'monto_pago',
        'estado_pago_revision',
        'estado_requisitos',
        'observacion_requisitos',
        'fecha_envio_requisitos',
        'fecha_validacion_requisitos',
        'archivo_fotocopia_ci',
        'archivo_titulo_bachiller',
        'archivo_certificado_nacimiento',
        'archivo_fotografia',
        'archivo_formulario_inscripcion',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'titulo_bachiller' => 'boolean',
        'estado_pago' => 'boolean',
        'doc_fotocopia_ci' => 'boolean',
        'doc_titulo_bachiller' => 'boolean',
        'doc_certificado_nacimiento' => 'boolean',
        'doc_fotografia' => 'boolean',
        'doc_formulario_inscripcion' => 'boolean',
        'monto_pago' => 'decimal:2',
        'fecha_envio_requisitos' => 'datetime',
        'fecha_validacion_requisitos' => 'datetime',
        'fecha_pago' => 'datetime',
    ];

    // Relación: Un postulante pertenece a un grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // Relación: Un postulante tiene una relación con las notas
    public function notas()
    {
        return $this->hasOne(Nota::class, 'postulante_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function carreraAsignada()
    {
        return $this->belongsTo(Carrera::class, 'carrera_asignada_id');
    }
}
