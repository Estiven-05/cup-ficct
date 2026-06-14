@extends('layouts.app')

@section('title', 'Dashboard Administrativo - CUP FICCT')
@section('page_kicker', 'Panel administrativo')
@section('page_title', 'Dashboard CUP FICCT')
@section('page_description', 'Control general de postulantes, docentes, grupos, notas, horarios y admisión por cupos.')


@section('content')
    @if(session('exito'))
        <div class="alert alert-success">
            {{ session('exito') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa los errores:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cup-stat-grid mb-4">
        <div class="card cup-stat primary">
            <span>Total inscritos</span>
            <strong>{{ $totalInscritos ?? 0 }}</strong>
            <small>Postulantes registrados</small>
        </div>

        <div class="card cup-stat success">
            <span>Aprobados</span>
            <strong class="text-success">{{ $totalAprobados ?? 0 }}</strong>
            <small>Con nota habilitante</small>
        </div>

        <div class="card cup-stat danger">
            <span>Reprobados</span>
            <strong class="text-danger">{{ $totalReprobados ?? 0 }}</strong>
            <small>No alcanzan el mínimo</small>
        </div>

        <div class="card cup-stat warning">
            <span>Grupos</span>
            <strong>{{ $totalGrupos ?? 0 }}</strong>
            <small>Paralelos activos</small>
        </div>
    </div>

    <div class="cup-stat-grid mb-4">
        <div class="card cup-stat">
            <span>Docentes</span>
            <strong>{{ $totalDocentes ?? 0 }}</strong>
            <small>Registrados en sistema</small>
        </div>

        <div class="card cup-stat success">
            <span>Docentes habilitados</span>
            <strong class="text-success">{{ $totalDocentesHabilitados ?? 0 }}</strong>
            <small>Disponibles para asignación</small>
        </div>

        <div class="card cup-stat">
            <span>Aulas disponibles</span>
            <strong class="text-primary">{{ $totalAulasDisponibles ?? 0 }}</strong>
            <small>Capacidad académica</small>
        </div>

        <div class="card cup-stat">
            <span>Horarios registrados</span>
            <strong>{{ $totalHorariosClases ?? 0 }}</strong>
            <small>Clases programadas</small>
        </div>
    </div>


    <div class="cup-stat-grid mb-4">
        <div class="card cup-stat success"><span>Admitidos</span><strong>{{ $totalAdmitidos ?? 0 }}</strong><small>Con cupo asignado</small></div>
        <div class="card cup-stat danger"><span>No admitidos</span><strong>{{ $totalNoAdmitidos ?? 0 }}</strong><small>Sin cupo o reprobados</small></div>
        <div class="card cup-stat warning"><span>Requisitos en revisión</span><strong>{{ $totalRequisitosRevision ?? 0 }}</strong><small>Pendientes de coordinación</small></div>
        <div class="card cup-stat warning"><span>Pagos en revisión</span><strong>{{ $totalPagosRevision ?? 0 }}</strong><small>Pasarela de pago CUP</small></div>
    </div>


    <div class="card mb-4">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="cup-section-title">Proceso de admisión por cupos</h2>
                <p class="cup-muted mb-0 mt-2">
                    Ordena a los postulantes aprobados por promedio y los asigna a carrera 1 o carrera 2 según los cupos disponibles.
                </p>
            </div>

            <form action="{{ route('admision.procesar') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning">
                    Procesar admisión por cupos
                </button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
            <div>
                <h2 class="cup-section-title">Listado general de postulantes</h2>
                <p class="cup-muted mb-0">Seguimiento académico y administrativo de admisión.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>CI</th>
                            <th>Nombre completo</th>
                            <th>Carrera 1</th>
                            <th>Carrera 2</th>
                            <th>Grupo</th>
                            <th>Promedio</th>
                            <th>Resultado CUP</th>
                            <th>Carrera asignada</th>
                            <th>Estado admisión</th>
                            <th>Tipo asignación</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($todosLosPostulantes ?? [] as $postulante)
                            <tr>
                                <td>{{ $postulante->id }}</td>
                                <td>{{ $postulante->ci }}</td>
                                <td class="text-start fw-semibold">{{ $postulante->nombres }} {{ $postulante->apellidos }}</td>
                                <td>{{ $postulante->carrera_1 }}</td>
                                <td>{{ $postulante->carrera_2 ?? 'No registrada' }}</td>
                                <td>{{ $postulante->grupo->nombre_grupo ?? 'Sin grupo' }}</td>

                                <td>
                                    @if($postulante->notas)
                                        {{ number_format($postulante->notas->promedio, 2) }}
                                    @else
                                        Pendiente
                                    @endif
                                </td>

                                <td>
                                    @if($postulante->notas)
                                        @if($postulante->notas->estado == 'APROBADO')
                                            <span class="badge bg-success">APROBADO</span>
                                        @else
                                            <span class="badge bg-danger">REPROBADO</span>
                                        @endif
                                    @else
                                        <span class="badge bg-warning text-dark">SIN NOTAS</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $postulante->carreraAsignada->nombre ?? 'Sin asignar' }}
                                </td>

                                <td>
                                    @if(($postulante->estado_admision ?? 'PENDIENTE') == 'ADMITIDO')
                                        <span class="badge bg-success">ADMITIDO</span>
                                    @elseif(($postulante->estado_admision ?? 'PENDIENTE') == 'NO_ADMITIDO')
                                        <span class="badge bg-danger">NO ADMITIDO</span>
                                    @else
                                        <span class="badge bg-warning text-dark">PENDIENTE</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $postulante->tipo_asignacion ?? 'Pendiente' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    No existen postulantes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h2 class="cup-section-title text-success">Aprobados</h2>
                </div>

                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($listaAprobados ?? [] as $postulante)
                            <li class="list-group-item px-0">
                                <strong>{{ $postulante->nombres }} {{ $postulante->apellidos }}</strong>
                                <br>
                                <small class="text-muted">
                                    Promedio: {{ number_format($postulante->notas->promedio ?? 0, 2) }}
                                </small>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-muted">Sin aprobados todavía.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h2 class="cup-section-title text-danger">Reprobados</h2>
                </div>

                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($listaReprobados ?? [] as $postulante)
                            <li class="list-group-item px-0">
                                <strong>{{ $postulante->nombres }} {{ $postulante->apellidos }}</strong>
                                <br>
                                <small class="text-muted">
                                    Promedio: {{ number_format($postulante->notas->promedio ?? 0, 2) }}
                                </small>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-muted">Sin reprobados todavía.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h2 class="cup-section-title text-warning">Pendientes</h2>
                </div>

                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($listaPendientes ?? [] as $postulante)
                            <li class="list-group-item px-0">
                                <strong>{{ $postulante->nombres }} {{ $postulante->apellidos }}</strong>
                                <br>
                                <small class="text-muted">Notas todavía no registradas.</small>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-muted">Sin pendientes.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
