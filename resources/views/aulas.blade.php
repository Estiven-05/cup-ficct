@extends('layouts.app')

@section('title', 'Gestión de Aulas - CUP FICCT')
@section('page_kicker', 'Infraestructura')
@section('page_title', 'Gestión de aulas')
@section('page_description', 'Administra aulas, capacidad disponible y estado operativo para la planificación de clases.')


@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
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
            <h2 class="cup-section-title">Registrar aula</h2>

        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('aulas.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Código del aula</label>
                        <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}" placeholder="Ej: Aula 101" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Pabellón / Módulo</label>
                        <input type="text" name="pabellon" class="form-control" value="{{ old('pabellon') }}" placeholder="Ej: Módulo 236">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Capacidad</label>
                        <input type="number" name="capacidad" class="form-control" value="{{ old('capacidad') }}" min="1" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="DISPONIBLE" {{ old('estado') == 'DISPONIBLE' ? 'selected' : '' }}>DISPONIBLE</option>
                            <option value="NO DISPONIBLE" {{ old('estado') == 'NO DISPONIBLE' ? 'selected' : '' }}>NO DISPONIBLE</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    Registrar aula
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="cup-section-title">Listado de aulas</h2>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Pabellón / Módulo</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($aulas as $aula)
                            <tr>
                                <td>{{ $aula->id }}</td>
                                <td class="fw-semibold">{{ $aula->codigo }}</td>
                                <td>{{ $aula->pabellon ?? 'Sin ubicación' }}</td>
                                <td>{{ $aula->capacidad }}</td>
                                <td>
                                    <span class="badge {{ $aula->estado === 'DISPONIBLE' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $aula->estado }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Todavía no hay aulas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
