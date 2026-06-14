@extends('layouts.app')

@section('title', 'Asignación de Docentes - CUP FICCT')
@section('page_kicker', 'Planificación académica')
@section('page_title', 'Asignación de docentes')
@section('page_description', 'Vincula docentes habilitados con grupos y materias donde tengan competencia aprobada.')


@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa los datos ingresados:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="cup-section-title">Asignar docente a grupo</h2>
            <p class="cup-muted mb-0 mt-1">Solo se permitirá asignar materias con competencia docente aprobada.</p>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('asignaciones.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Docente habilitado</label>
                        <select name="docente_id" class="form-select" required>
                            <option value="">Seleccione un docente</option>
                            @foreach($docentes as $docente)
                                <option value="{{ $docente->id }}" {{ old('docente_id') == $docente->id ? 'selected' : '' }}>
                                    {{ $docente->nombres }} {{ $docente->apellidos }}
                                    | Competencias: {{ $docente->competencias->pluck('materia')->implode(', ') ?: 'Sin competencias aprobadas' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Grupo</label>
                        <select name="grupo_id" class="form-select" required>
                            <option value="">Seleccione un grupo</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}" {{ old('grupo_id') == $grupo->id ? 'selected' : '' }}>
                                    {{ $grupo->nombre_grupo }} | Inscritos: {{ $grupo->total_inscritos }}/{{ $grupo->cupo_maximo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Materia</label>
                        <select name="materia" class="form-select" required>
                            <option value="">Seleccione una materia</option>
                            <option value="Computación" {{ old('materia') == 'Computación' ? 'selected' : '' }}>Computación</option>
                            <option value="Matemáticas" {{ old('materia') == 'Matemáticas' ? 'selected' : '' }}>Matemáticas</option>
                            <option value="Inglés" {{ old('materia') == 'Inglés' ? 'selected' : '' }}>Inglés</option>
                            <option value="Física" {{ old('materia') == 'Física' ? 'selected' : '' }}>Física</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Asignar docente</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="cup-section-title">Listado de asignaciones</h2>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Docente</th>
                            <th>Grupo</th>
                            <th>Materia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                            <tr>
                                <td>{{ $asignacion->id }}</td>
                                <td class="fw-semibold">
                                    {{ $asignacion->docente->nombres ?? 'Sin docente' }}
                                    {{ $asignacion->docente->apellidos ?? '' }}
                                </td>
                                <td>{{ $asignacion->grupo->nombre_grupo ?? 'Sin grupo' }}</td>
                                <td>{{ $asignacion->materia }}</td>
                                <td><span class="badge bg-success">{{ $asignacion->estado }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Todavía no hay docentes asignados a grupos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
