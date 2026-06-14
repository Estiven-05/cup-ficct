@extends('layouts.app')

@section('title', 'Bitácora - CUP FICCT')
@section('page_kicker', 'Seguridad y usuarios')
@section('page_title', 'Bitácora del sistema')
@section('page_description', 'Auditoría de acciones relevantes ejecutadas por usuarios del sistema.')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h2 class="cup-section-title">Filtros de auditoría</h2>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ url('/bitacora') }}">
            <div class="cup-filter-grid">
                <div>
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" value="{{ request('usuario') }}">
                </div>
                <div>
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select">
                        <option value="">Todos</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol }}" {{ request('rol') == $rol ? 'selected' : '' }}>{{ strtoupper($rol) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Módulo</label>
                    <select name="modulo" class="form-select">
                        <option value="">Todos</option>
                        @foreach($modulos as $modulo)
                            <option value="{{ $modulo }}" {{ request('modulo') == $modulo ? 'selected' : '' }}>{{ $modulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Acción</label>
                    <input type="text" name="accion" class="form-control" value="{{ request('accion') }}">
                </div>
                <div>
                    <label class="form-label">Fecha desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                </div>
                <div>
                    <label class="form-label">Fecha hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                </div>
            </div>

            <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                <button type="submit" class="btn btn-primary">Filtrar bitácora</button>
                <a href="{{ url('/bitacora') }}" class="btn btn-outline-secondary">Limpiar filtros</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="cup-section-title">Eventos registrados</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle cup-report-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bitacoras as $bitacora)
                        <tr>
                            <td>{{ optional($bitacora->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $bitacora->nombre_usuario ?? 'No registrado' }}</td>
                            <td><span class="badge bg-secondary">{{ strtoupper($bitacora->rol ?? 'N/A') }}</span></td>
                            <td>{{ $bitacora->modulo }}</td>
                            <td class="fw-semibold">{{ $bitacora->accion }}</td>
                            <td class="text-wrap">{{ $bitacora->descripcion }}</td>
                            <td>{{ $bitacora->ip ?? 'Sin IP' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay eventos para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $bitacoras->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
