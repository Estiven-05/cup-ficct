@extends('layouts.app')

@section('title', 'Postulantes - CUP FICCT')
@section('page_kicker', 'Inscripción y postulantes')
@section('page_title', 'Postulantes inscritos')
@section('page_description', 'Busca, lista, edita y desactiva registros de postulantes sin borrar historial académico.')

@section('page_actions')
    <a href="{{ url('/inscripcion') }}" class="btn btn-primary">Inscribir postulante</a>
@endsection

@section('content')
@if(session('exito'))
    <div class="alert alert-success">{{ session('exito') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h2 class="cup-section-title">Búsqueda de postulantes</h2></div>
    <div class="card-body">
        <form method="GET" action="{{ route('postulantes.index') }}" class="row g-3">
            <div class="col-lg-3 col-md-6"><label class="form-label">Buscar</label><input name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="CI, nombre, correo, carrera"></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Carrera</label><select name="carrera" class="form-select"><option value="">Todas</option>@foreach($carreras ?? [] as $carrera)<option value="{{ $carrera->nombre }}" {{ request('carrera') == $carrera->nombre ? 'selected' : '' }}>{{ $carrera->nombre }}</option>@endforeach</select></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Grupo</label><select name="grupo_id" class="form-select"><option value="">Todos</option>@foreach($grupos ?? [] as $grupo)<option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>{{ $grupo->nombre_grupo }}</option>@endforeach</select></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Estado académico</label><select name="estado_academico" class="form-select"><option value="">Todos</option>@foreach(['APROBADO','REPROBADO','SIN_NOTAS'] as $estado)<option value="{{ $estado }}" {{ request('estado_academico') == $estado ? 'selected' : '' }}>{{ str_replace('_', ' ', $estado) }}</option>@endforeach</select></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Estado admisión</label><select name="estado_admision" class="form-select"><option value="">Todos</option>@foreach(['ADMITIDO','NO_ADMITIDO','PENDIENTE'] as $estado)<option value="{{ $estado }}" {{ request('estado_admision') == $estado ? 'selected' : '' }}>{{ str_replace('_', ' ', $estado) }}</option>@endforeach</select></div>
            <div class="col-lg-1 col-md-6"><label class="form-label">Registro</label><select name="estado_registro" class="form-select"><option value="">Todos</option><option value="ACTIVO" {{ request('estado_registro') == 'ACTIVO' ? 'selected' : '' }}>Activo</option><option value="INACTIVO" {{ request('estado_registro') == 'INACTIVO' ? 'selected' : '' }}>Inactivo</option></select></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-primary">Buscar</button><a href="{{ route('postulantes.index') }}" class="btn btn-outline-secondary">Limpiar filtros</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>
            <h2 class="cup-section-title">Registro general de alumnos inscritos</h2>
            <p class="cup-muted mb-0">Total mostrado: {{ $postulantes->count() }}</p>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th><th>CI</th><th>Nombre completo</th><th>Correo</th><th>Teléfono</th><th>Ciudad</th><th>Carrera 1</th><th>Grupo</th><th>Estado CUP</th><th>Registro</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($postulantes as $p)
                    <tr>
                        <td class="fw-bold">#{{ $p->id }}</td>
                        <td>{{ $p->ci }}</td>
                        <td class="fw-semibold">{{ $p->nombres }} {{ $p->apellidos }}</td>
                        <td>{{ $p->user->email ?? 'Sin correo' }}</td>
                        <td>{{ $p->telefono ?? 'No registrado' }}</td>
                        <td>{{ $p->ciudad ?? 'No registrada' }}</td>
                        <td>{{ $p->carrera_1 }}</td>
                        <td><span class="badge bg-secondary">{{ $p->grupo->nombre_grupo ?? 'Sin grupo' }}</span></td>
                        <td>
                            @if($p->notas)
                                <span class="badge {{ $p->notas->estado == 'APROBADO' ? 'bg-success' : 'bg-danger' }}">{{ $p->notas->estado }}</span>
                            @else
                                <span class="badge bg-warning text-dark">SIN NOTAS</span>
                            @endif
                        </td>
                        <td><span class="badge {{ ($p->estado_registro ?? 'ACTIVO') === 'ACTIVO' ? 'bg-success' : 'bg-secondary' }}">{{ $p->estado_registro ?? 'ACTIVO' }}</span></td>
                        <td>
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <a href="{{ route('postulantes.editar', $p->id) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                @if(($p->estado_registro ?? 'ACTIVO') !== 'INACTIVO')
                                    <form method="POST" action="{{ route('postulantes.desactivar', $p->id) }}" onsubmit="return confirm('¿Desactivar este postulante? No se eliminará su historial.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Desactivar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-4 text-muted">No existen postulantes con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection