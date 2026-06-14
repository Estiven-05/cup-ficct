@extends('layouts.app')

@section('title', 'Módulo de Calificaciones - CUP FICCT')
@section('page_kicker', 'Evaluaciones')
@section('page_title', 'Registro de notas')
@section('page_description', 'Registra una materia por envío, respetando la regla 30 + 30 + 40.')

@section('content')
    @if(session('exito'))
        <div class="alert alert-success fw-bold">
            {{ session('exito') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa los datos ingresados:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(auth()->user()->role === 'docente' && !$docenteActual)
        <div class="alert alert-warning">
            Tu usuario docente todavía no está vinculado a un registro de docente. Pide a coordinación revisar tu correo o importación.
        </div>
    @endif


    @if(!empty($estadisticasMaterias))
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="cup-section-title">Estadísticas por materia</h2>
                <p class="cup-muted mb-0">Promedios y aprobación calculados desde notas registradas.</p>
            </div>
            <div class="card-body">
                <div class="cup-stat-grid">
                    @foreach($estadisticasMaterias as $materia => $datos)
                        <div class="card cup-stat">
                            <span>{{ $materia }}</span>
                            <strong>{{ number_format($datos['promedio'], 2) }}</strong>
                            <small>{{ $datos['aprobados'] }} aprobados / {{ $datos['reprobados'] }} reprobados</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="cup-section-title">Evaluaciones preuniversitarias</h2>
            <p class="cup-muted mb-0 mt-1">
                Evaluación 1 y 2 sobre 30 puntos, evaluación final sobre 40 puntos.
            </p>
        </div>

        <div class="card-body">
            <form action="{{ url('/guardar-notas') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Postulante</label>
                        <select name="postulante_id" class="form-select" required>
                            <option value="">Seleccione postulante</option>
                            @foreach($postulantes as $postulante)
                                <option value="{{ $postulante->id }}" {{ old('postulante_id') == $postulante->id ? 'selected' : '' }}>
                                    #{{ $postulante->id }} - {{ $postulante->nombres }} {{ $postulante->apellidos }}
                                    - {{ $postulante->grupo->nombre_grupo ?? 'Sin grupo' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Materia</label>
                        <select name="materia" class="form-select" required>
                            <option value="">Seleccione materia</option>
                            @foreach($materiasDisponibles as $materia)
                                <option value="{{ $materia }}" {{ old('materia') == $materia ? 'selected' : '' }}>
                                    {{ $materia }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Evaluación 1 /30</label>
                        <input type="number" name="eval1" class="form-control" placeholder="0 - 30" min="0" max="30" step="0.01" value="{{ old('eval1') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Evaluación 2 /30</label>
                        <input type="number" name="eval2" class="form-control" placeholder="0 - 30" min="0" max="30" step="0.01" value="{{ old('eval2') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Evaluación final /40</label>
                        <input type="number" name="eval3" class="form-control" placeholder="0 - 40" min="0" max="40" step="0.01" value="{{ old('eval3') }}" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    Guardar notas de la materia
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="cup-section-title">Postulantes disponibles</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Postulante</th>
                            <th>Grupo</th>
                            <th>Promedio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($postulantes as $postulante)
                            <tr>
                                <td>{{ $postulante->id }}</td>
                                <td class="fw-semibold">{{ $postulante->nombres }} {{ $postulante->apellidos }}</td>
                                <td>{{ $postulante->grupo->nombre_grupo ?? 'Sin grupo' }}</td>
                                <td>{{ $postulante->notas ? number_format($postulante->notas->promedio, 2) : 'Pendiente' }}</td>
                                <td>{{ $postulante->notas->estado ?? 'SIN NOTAS' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay postulantes disponibles para registrar notas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
