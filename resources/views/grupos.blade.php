@extends('layouts.app')

@section('title', 'Grupos CUP - FICCT')
@section('page_kicker', 'Gestión académica CUP')
@section('page_title', 'Módulo formal de grupos')
@section('page_description', '')

@section('content')
@if(session('exito'))
    <div class="alert alert-success">{{ session('exito') }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="cup-stat-grid-3 mb-4">
    <div class="card cup-stat primary">
        <span>Total inscritos</span>
        <strong>{{ $totalInscritos }}</strong>
        <small>Postulantes activos</small>
    </div>
    <div class="card cup-stat success">
        <span>GRUPOS REQUERIDOS</span>
        <strong>{{ $gruposNecesarios }}</strong>
        <small class="text-success text-uppercase fw-bold mt-2 d-block" style="font-size: 0.78rem; letter-spacing: 0.05em;">
            {{ $gruposNecesarios === 1 ? 'GRUPO REQUERIDO' : 'GRUPOS REQUERIDOS' }}
        </small>
    </div>
    <div class="card cup-stat">
        <span>Grupos habilitados BD</span>
        <strong>{{ $gruposHabilitados }}</strong>
        <small>Registrados actualmente</small>
    </div>
</div>

@if($inconsistencias->count())
    <div class="alert alert-warning">Hay grupos con más de 70 postulantes. Revisa la distribución antes de nuevas asignaciones.</div>
@endif

<div class="card mb-4">
    <div class="card-body d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
        <div>
            <h2 class="cup-section-title">Acciones de grupos</h2>
            <p class="cup-muted mb-0">Recalcula conteos y normaliza nombres M001/T001/N001 sin eliminar registros.</p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <form method="POST" action="{{ route('grupos.recalcular') }}">@csrf<button class="btn btn-primary">Recalcular grupos</button></form>
            <form method="POST" action="{{ route('grupos.normalizar') }}">@csrf<button class="btn btn-outline-primary">Normalizar nomenclatura</button></form>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h2 class="cup-section-title">Grupos con más postulantes</h2></div>
            <div class="card-body">
                @foreach($gruposConMasPostulantes as $grupo)
                    <div class="cup-info-item mb-2"><span>{{ $grupo->nombre_grupo }}</span><strong>{{ $grupo->postulantes->count() }} postulantes</strong></div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h2 class="cup-section-title">Grupos con más aprobados</h2></div>
            <div class="card-body">
                @foreach($gruposConMasAprobados as $grupo)
                    <div class="cup-info-item mb-2"><span>{{ $grupo->nombre_grupo }}</span><strong>{{ $grupo->postulantes->filter(fn($p) => optional($p->notas)->estado === 'APROBADO')->count() }} aprobados</strong></div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2 class="cup-section-title">Tabla de grupos</h2></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>ID</th><th>Grupo</th><th>Turno</th><th>Cupo máximo</th><th>Ocupados</th><th>Disponibles</th><th>Estado</th><th>Postulantes</th></tr></thead>
                <tbody>
                    @forelse($grupos as $grupo)
                        @php $ocupados = $grupo->postulantes->count(); @endphp
                        <tr>
                            <td>#{{ $grupo->id }}</td>
                            <td class="fw-semibold">{{ $grupo->nombre_grupo }}</td>
                            <td>{{ $grupo->turno ?? 'MAÑANA' }}</td>
                            <td>{{ $grupo->cupo_maximo ?? 70 }}</td>
                            <td>{{ $ocupados }}</td>
                            <td>{{ max(0, ($grupo->cupo_maximo ?? 70) - $ocupados) }}</td>
                            <td><span class="badge {{ $ocupados > 70 ? 'bg-danger' : 'bg-success' }}">{{ $ocupados > 70 ? 'INCONSISTENTE' : ($grupo->estado ?? 'ACTIVO') }}</span></td>
                            <td>
                                <details>
                                    <summary>Ver listado</summary>
                                    <ul class="mt-2 mb-0 small">
                                        @forelse($grupo->postulantes as $postulante)
                                            <li>{{ $postulante->ci }} - {{ $postulante->nombres }} {{ $postulante->apellidos }}</li>
                                        @empty
                                            <li>Sin postulantes asignados.</li>
                                        @endforelse
                                    </ul>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No hay grupos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection