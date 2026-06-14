@extends('layouts.app')

@section('title', 'Editar postulante - CUP FICCT')
@section('page_kicker', 'Inscripción y postulantes')
@section('page_title', 'Editar postulante')
@section('page_description', 'Actualiza datos personales sin alterar notas, requisitos ni admisión.')

@section('page_actions')
    <a href="{{ route('postulantes.index') }}" class="btn btn-outline-primary">Volver a postulantes</a>
@endsection

@section('content')
@if($errors->any())
    <div class="alert alert-danger"><strong>Revisa los datos:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('postulantes.actualizar', $postulante->id) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">CI</label><input name="ci" class="form-control" value="{{ old('ci', $postulante->ci) }}" required></div>
                <div class="col-md-4"><label class="form-label">Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $postulante->nombres) }}" required></div>
                <div class="col-md-4"><label class="form-label">Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $postulante->apellidos) }}" required></div>
                <div class="col-md-4"><label class="form-label">Fecha de nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento', optional($postulante->fecha_nacimiento)->format('Y-m-d')) }}"></div>
                <div class="col-md-4"><label class="form-label">Sexo</label><select name="sexo" class="form-select"><option value="">Seleccione...</option>@foreach(['MASCULINO','FEMENINO','OTRO'] as $sexo)<option value="{{ $sexo }}" {{ old('sexo', $postulante->sexo) === $sexo ? 'selected' : '' }}>{{ $sexo }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" value="{{ old('telefono', $postulante->telefono) }}"></div>
                <div class="col-md-6"><label class="form-label">Correo electrónico</label><input type="email" name="correo" class="form-control" value="{{ old('correo', optional($postulante->user)->email) }}" required></div>
                <div class="col-md-6"><label class="form-label">Ciudad</label><input name="ciudad" class="form-control" value="{{ old('ciudad', $postulante->ciudad) }}"></div>
                <div class="col-md-12"><label class="form-label">Dirección</label><input name="direccion" class="form-control" value="{{ old('direccion', $postulante->direccion) }}"></div>
                <div class="col-md-6"><label class="form-label">Colegio de procedencia</label><input name="colegio_procedencia" class="form-control" value="{{ old('colegio_procedencia', $postulante->colegio_procedencia) }}"></div>
                <div class="col-md-3"><label class="form-label">Carrera 1</label><input name="carrera_1" class="form-control" value="{{ old('carrera_1', $postulante->carrera_1) }}" required></div>
                <div class="col-md-3"><label class="form-label">Carrera 2</label><input name="carrera_2" class="form-control" value="{{ old('carrera_2', $postulante->carrera_2) }}"></div>
                <div class="col-md-4"><label class="form-label">Grupo</label><select name="grupo_id" class="form-select"><option value="">Sin grupo</option>@foreach($grupos as $grupo)<option value="{{ $grupo->id }}" {{ old('grupo_id', $postulante->grupo_id) == $grupo->id ? 'selected' : '' }}>{{ $grupo->nombre_grupo }}</option>@endforeach</select></div>
                <div class="col-md-8"><label class="form-label">Observación</label><input name="observacion_postulante" class="form-control" value="{{ old('observacion_postulante', $postulante->observacion_postulante) }}"></div>
            </div>
            <button class="btn btn-primary mt-4">Guardar cambios</button>
        </form>
    </div>
</div>
@endsection