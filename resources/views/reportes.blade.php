@extends('layouts.app')

@section('title', 'Reportes - CUP FICCT')
@section('page_kicker', 'ANÁLISIS ACADÉMICO')
@section('page_title', 'Reportes dinámicos')
@section('page_description', 'Consulta postulantes, carreras, grupos y docentes con filtros combinables en pantalla.')

@section('page_actions')
    <a href="{{ route('reportes.index') }}" class="btn btn-outline-primary">Limpiar filtros</a>
@endsection

@section('content')
@php
    $estadoAcademico = function ($postulante) {
        return $postulante->notas->estado ?? 'SIN NOTAS';
    };

    $badgeEstado = function ($estado) {
        return match ($estado) {
            'APROBADO', 'ADMITIDO' => 'bg-success',
            'REPROBADO', 'NO_ADMITIDO', 'RECHAZADO' => 'bg-danger',
            'EN_REVISION', 'PENDIENTE', 'SIN NOTAS' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    };

    $textoRevision = function ($estado) {
        return match ($estado ?? 'PENDIENTE') {
            'EN_REVISION' => 'EN REVISIÓN',
            'APROBADO' => 'APROBADO',
            'RECHAZADO' => 'RECHAZADO',
            default => 'PENDIENTE',
        };
    };
@endphp

<div class="card mb-4">
    <div class="card-header">
        <h2 class="cup-section-title">Filtros de consulta</h2>
        <p class="cup-muted mb-0">Combina filtros y consulta los reportes sin exportar archivos.</p>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('reportes.index') }}">
            <div class="cup-filter-grid">
                <div>
                    <label class="form-label">Carrera 1</label>
                    <select name="carrera_1" class="form-select">
                        <option value="">Todas</option>
                        @foreach($carrerasPostulantes as $carrera)
                            <option value="{{ $carrera }}" {{ request('carrera_1') == $carrera ? 'selected' : '' }}>
                                {{ $carrera }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Carrera asignada</label>
                    <select name="carrera_asignada_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera->id }}" {{ request('carrera_asignada_id') == $carrera->id ? 'selected' : '' }}>
                                {{ $carrera->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Grupo</label>
                    <select name="grupo_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nombre_grupo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Estado académico</label>
                    <select name="estado_academico" class="form-select">
                        <option value="">Todos</option>
                        @foreach(['APROBADO', 'REPROBADO', 'SIN_NOTAS'] as $estado)
                            <option value="{{ $estado }}" {{ request('estado_academico') == $estado ? 'selected' : '' }}>
                                {{ $estado === 'SIN_NOTAS' ? 'SIN NOTAS' : $estado }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Estado admisión</label>
                    <select name="estado_admision" class="form-select">
                        <option value="">Todos</option>
                        @foreach(['ADMITIDO', 'NO_ADMITIDO', 'PENDIENTE'] as $estado)
                            <option value="{{ $estado }}" {{ request('estado_admision') == $estado ? 'selected' : '' }}>
                                {{ $estado === 'NO_ADMITIDO' ? 'NO ADMITIDO' : $estado }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Estado requisitos</label>
                    <select name="estado_requisitos" class="form-select">
                        <option value="">Todos</option>
                        @foreach($estadosRevision as $estado)
                            <option value="{{ $estado }}" {{ request('estado_requisitos') == $estado ? 'selected' : '' }}>
                                {{ $textoRevision($estado) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Estado pago</label>
                    <select name="estado_pago_revision" class="form-select">
                        <option value="">Todos</option>
                        @foreach($estadosRevision as $estado)
                            <option value="{{ $estado }}" {{ request('estado_pago_revision') == $estado ? 'selected' : '' }}>
                                {{ $textoRevision($estado) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Docente</label>
                    <select name="docente_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($docentes as $docente)
                            <option value="{{ $docente->id }}" {{ request('docente_id') == $docente->id ? 'selected' : '' }}>
                                {{ $docente->nombres }} {{ $docente->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Materia</label>
                    <select name="materia" class="form-select">
                        <option value="">Todas</option>
                        @foreach($materias as $materia)
                            <option value="{{ $materia }}" {{ request('materia') == $materia ? 'selected' : '' }}>
                                {{ $materia }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Turno</label>
                    <select name="turno" class="form-select">
                        <option value="">Todos</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno }}" {{ request('turno') == $turno ? 'selected' : '' }}>
                                {{ $turno }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Modalidad</label>
                    <select name="modalidad" class="form-select">
                        <option value="">Todas</option>
                        @foreach($modalidades as $modalidad)
                            <option value="{{ $modalidad }}" {{ request('modalidad') == $modalidad ? 'selected' : '' }}>
                                {{ $modalidad }}
                            </option>
                        @endforeach
                    </select>
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
                <button type="submit" class="btn btn-primary">Consultar reportes</button>
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">Limpiar filtros</a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <h2 class="cup-section-title">Exportación Excel/CSV y PDF</h2>
            <p class="cup-muted mb-0">Descarga CSV compatibles con Excel o abre una versión imprimible con los filtros actuales.</p>
        </div>
        <div class="cup-export-actions">
            <a href="{{ route('reportes.exportar.postulantes', request()->query()) }}" class="btn btn-outline-primary">Exportar postulantes a Excel/CSV</a>
            <a href="{{ route('reportes.exportar.carreras', request()->query()) }}" class="btn btn-outline-primary">Exportar carreras a Excel/CSV</a>
            <a href="{{ route('reportes.exportar.grupos', request()->query()) }}" class="btn btn-outline-primary">Exportar grupos a Excel/CSV</a>
            <a href="{{ route('reportes.exportar.docentes', request()->query()) }}" class="btn btn-outline-primary">Exportar docentes a Excel/CSV</a>
            <a href="{{ route('reportes.imprimir', request()->query()) }}" class="btn btn-primary" target="_blank" rel="noopener">Ver/Imprimir reporte PDF</a>
        </div>
    </div>
</div>

<div class="cup-stat-grid mb-4">
    <div class="card cup-stat primary">
        <span>Total postulantes</span>
        <strong>{{ $resumen['total_postulantes'] }}</strong>
    </div>
    <div class="card cup-stat">
        <span>Con notas</span>
        <strong>{{ $resumen['total_con_notas'] }}</strong>
    </div>
    <div class="card cup-stat success">
        <span>Aprobados</span>
        <strong>{{ $resumen['total_aprobados'] }}</strong>
    </div>
    <div class="card cup-stat danger">
        <span>Reprobados</span>
        <strong>{{ $resumen['total_reprobados'] }}</strong>
    </div>
    <div class="card cup-stat success">
        <span>Admitidos</span>
        <strong>{{ $resumen['total_admitidos'] }}</strong>
    </div>
    <div class="card cup-stat danger">
        <span>No admitidos</span>
        <strong>{{ $resumen['total_no_admitidos'] }}</strong>
    </div>
    <div class="card cup-stat warning">
        <span>Pendientes</span>
        <strong>{{ $resumen['total_pendientes'] }}</strong>
    </div>
    <div class="card cup-stat success">
        <span>Req. aprobados</span>
        <strong>{{ $resumen['total_requisitos_aprobados'] }}</strong>
    </div>
    <div class="card cup-stat success">
        <span>Pago aprobado</span>
        <strong>{{ $resumen['total_pago_aprobado'] }}</strong>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h2 class="cup-section-title">Reporte de postulantes</h2>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle cup-report-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CI</th>
                        <th>Nombre completo</th>
                        <th>Carrera 1</th>
                        <th>Carrera 2</th>
                        <th>Carrera asignada</th>
                        <th>Grupo</th>
                        <th>Promedio</th>
                        <th>Estado académico</th>
                        <th>Estado admisión</th>
                        <th>Estado requisitos</th>
                        <th>Estado pago</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($postulantes as $postulante)
                        @php
                            $estadoCup = $estadoAcademico($postulante);
                            $estadoAdmision = $postulante->estado_admision ?? 'PENDIENTE';
                        @endphp
                        <tr>
                            <td>{{ $postulante->id }}</td>
                            <td>{{ $postulante->ci }}</td>
                            <td class="fw-semibold text-wrap">{{ $postulante->nombres }} {{ $postulante->apellidos }}</td>
                            <td class="text-wrap">{{ $postulante->carrera_1 }}</td>
                            <td class="text-wrap">{{ $postulante->carrera_2 ?? 'No registrada' }}</td>
                            <td class="text-wrap">{{ $postulante->carreraAsignada->nombre ?? 'Sin asignar' }}</td>
                            <td>{{ $postulante->grupo->nombre_grupo ?? 'Sin grupo' }}</td>
                            <td>{{ $postulante->notas ? number_format($postulante->notas->promedio, 2) : 'Pendiente' }}</td>
                            <td><span class="badge {{ $badgeEstado($estadoCup) }}">{{ str_replace('_', ' ', $estadoCup) }}</span></td>
                            <td><span class="badge {{ $badgeEstado($estadoAdmision) }}">{{ str_replace('_', ' ', $estadoAdmision) }}</span></td>
                            <td><span class="badge {{ $badgeEstado($postulante->estado_requisitos ?? 'PENDIENTE') }}">{{ $textoRevision($postulante->estado_requisitos) }}</span></td>
                            <td><span class="badge {{ $badgeEstado($postulante->estado_pago_revision ?? 'PENDIENTE') }}">{{ $textoRevision($postulante->estado_pago_revision) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">No hay postulantes para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h2 class="cup-section-title">Reporte por carrera</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle cup-report-table">
                        <thead>
                            <tr>
                                <th>Carrera</th>
                                <th>Cupo máximo</th>
                                <th>Cupos ocupados</th>
                                <th>Admitidos filtrados</th>
                                <th>Cupos disponibles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reporteCarreras as $fila)
                                <tr>
                                    <td class="fw-semibold text-wrap">{{ $fila['carrera'] }}</td>
                                    <td>{{ $fila['cupo_maximo'] }}</td>
                                    <td>{{ $fila['cupos_ocupados'] }}</td>
                                    <td>{{ $fila['postulantes_admitidos'] }}</td>
                                    <td>{{ $fila['cupos_disponibles'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h2 class="cup-section-title">Reporte por grupo</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle cup-report-table">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Total</th>
                                <th>Aprobados</th>
                                <th>Reprobados</th>
                                <th>Admitidos</th>
                                <th>Promedio grupo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reporteGrupos as $fila)
                                <tr>
                                    <td class="fw-semibold">{{ $fila['grupo'] }}</td>
                                    <td>{{ $fila['total_postulantes'] }}</td>
                                    <td>{{ $fila['aprobados'] }}</td>
                                    <td>{{ $fila['reprobados'] }}</td>
                                    <td>{{ $fila['admitidos'] }}</td>
                                    <td>{{ is_null($fila['promedio_general']) ? 'Sin notas' : number_format($fila['promedio_general'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="cup-section-title">Reporte por docente</h2>
        <p class="cup-muted mb-0">Métricas inferidas desde asignaciones docentes y postulantes vinculados a sus grupos.</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle cup-report-table">
                <thead>
                    <tr>
                        <th>Docente</th>
                        <th>Materia</th>
                        <th>Grupos asignados</th>
                        <th>Postulantes vinculados</th>
                        <th>Aprobados</th>
                        <th>% aprobados</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reporteDocentes as $fila)
                        <tr>
                            <td class="fw-semibold text-wrap">{{ $fila['docente'] }}</td>
                            <td>{{ $fila['materia'] }}</td>
                            <td class="text-wrap">{{ $fila['grupos_asignados'] ?: 'Sin grupos' }}</td>
                            <td>{{ $fila['postulantes_vinculados'] }}</td>
                            <td>{{ $fila['aprobados'] }}</td>
                            <td>{{ number_format($fila['porcentaje_aprobados'], 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay asignaciones docentes para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
