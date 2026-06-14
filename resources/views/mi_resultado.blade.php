@extends('layouts.app')

@section('title', 'Mi Resultado de Admisión - CUP FICCT')
@section('page_kicker', 'Postulante')
@section('page_title', 'Mi resultado de admisión')
@section('page_description', 'Consulta tus datos personales, requisitos, pago, notas, promedio final y resultado de admisión.')

@section('content')
    @if(!$postulante)
        <div class="alert alert-warning">
            <strong>No se encontró información del postulante.</strong><br>
            Tu usuario existe, pero todavía no está vinculado a un registro de postulante.
        </div>
    @else
        @php
            $badgeClass = function ($estado) {
                return match ($estado) {
                    'APROBADO' => 'cup-badge-aprobado',
                    'RECHAZADO' => 'cup-badge-rechazado',
                    'EN_REVISION' => 'cup-badge-revision',
                    default => 'cup-badge-pendiente',
                };
            };

            $estadoTexto = function ($estado) {
                return match ($estado) {
                    'EN_REVISION' => 'EN REVISIÓN',
                    'APROBADO' => 'APROBADO',
                    'RECHAZADO' => 'RECHAZADO',
                    default => 'PENDIENTE',
                };
            };

            $estadoAdminTexto = match ($postulante->estado_inscripcion) {
                'evaluado' => 'EVALUADO',
                'habilitado_cup' => 'HABILITADO CUP',
                'observado' => 'OBSERVADO',
                default => 'PENDIENTE',
            };

            $estadoAdminBadge = match ($postulante->estado_inscripcion) {
                'habilitado_cup' => 'cup-badge-aprobado',
                'observado' => 'cup-badge-rechazado',
                'evaluado' => 'cup-badge-aprobado',
                default => 'cup-badge-pendiente',
            };
        @endphp

        <div class="card mb-4">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
                <div>
                    <h2 class="cup-section-title">Estado de requisitos y pago</h2>
                    <p class="cup-muted mb-0">Seguimiento administrativo previo a la habilitación del CUP.</p>
                </div>
            </div>

            <div class="card-body">
                <div class="cup-info-grid">
                    <div class="cup-info-item">
                        <span>Estado de requisitos</span>
                        <strong>
                            <span class="badge {{ $badgeClass($postulante->estado_requisitos ?? 'PENDIENTE') }}">
                                {{ $estadoTexto($postulante->estado_requisitos ?? 'PENDIENTE') }}
                            </span>
                        </strong>
                    </div>

                    <div class="cup-info-item">
                        <span>Estado de pago</span>
                        <strong>
                            <span class="badge {{ $badgeClass($postulante->estado_pago_revision ?? 'PENDIENTE') }}">
                                {{ $estadoTexto($postulante->estado_pago_revision ?? 'PENDIENTE') }}
                            </span>
                        </strong>
                    </div>

                    <div class="cup-info-item">
                        <span>Estado administrativo</span>
                        <strong>
                            <span class="badge {{ $estadoAdminBadge }}">
                                {{ $estadoAdminTexto }}
                            </span>
                        </strong>
                    </div>

                    <div class="cup-info-item">
                        <span>Observación del coordinador</span>
                        <strong>{{ $postulante->observacion_requisitos ?? 'Sin observación registrada.' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="cup-section-title">Datos del postulante</h2>
            </div>

            <div class="card-body">
                <div class="cup-info-grid">
                    <div class="cup-info-item">
                        <span>CI</span>
                        <strong>{{ $postulante->ci }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Nombre completo</span>
                        <strong>{{ $postulante->nombres }} {{ $postulante->apellidos }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Fecha de nacimiento</span>
                        <strong>{{ $postulante->fecha_nacimiento ? \Carbon\Carbon::parse($postulante->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Sexo</span>
                        <strong>{{ $postulante->sexo ?? 'No registrado' }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Teléfono</span>
                        <strong>{{ $postulante->telefono ?? 'No registrado' }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Ciudad</span>
                        <strong>{{ $postulante->ciudad ?? 'No registrada' }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Dirección</span>
                        <strong>{{ $postulante->direccion ?? 'No registrada' }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Carrera 1</span>
                        <strong>{{ $postulante->carrera_1 }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Carrera 2</span>
                        <strong>{{ $postulante->carrera_2 ?? 'No registrada' }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Colegio de procedencia</span>
                        <strong>{{ $postulante->colegio_procedencia ?? 'No registrado' }}</strong>
                    </div>
                    <div class="cup-info-item">
                        <span>Grupo asignado</span>
                        <strong>{{ $postulante->grupo->nombre_grupo ?? 'Sin grupo asignado' }}</strong>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mb-4">
            <div class="card-header">
                <h2 class="cup-section-title">Datos del pago CUP</h2>
                <p class="cup-muted mb-0">Resumen de la pasarela de pago CUP registrada para revisión administrativa.</p>
            </div>
            <div class="card-body">
                <div class="cup-info-grid">
                    <div class="cup-info-item"><span>Método de pago</span><strong>{{ $postulante->metodo_pago ?? 'No registrado' }}</strong></div>
                    <div class="cup-info-item"><span>Código de transacción</span><strong>{{ $postulante->codigo_transaccion ?? 'No registrado' }}</strong></div>
                    <div class="cup-info-item"><span>Monto</span><strong>{{ $postulante->monto_pago ? 'Bs ' . number_format((float) $postulante->monto_pago, 2) : 'No registrado' }}</strong></div>
                    <div class="cup-info-item"><span>Fecha/hora de pago</span><strong>{{ $postulante->fecha_pago ? \Carbon\Carbon::parse($postulante->fecha_pago)->format('d/m/Y H:i') : 'No registrada' }}</strong></div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="cup-section-title">Mi horario CUP</h2>
                <p class="cup-muted mb-0">Información de grupo, materias, docentes, aula, turno y modalidad.</p>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Materia</th>
                                <th>Docente</th>
                                <th>Día</th>
                                <th>Hora inicio</th>
                                <th>Hora fin</th>
                                <th>Turno</th>
                                <th>Modalidad</th>
                                <th>Aula</th>
                                <th>Examen presencial</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($horariosCup as $horario)
                                <tr>
                                    <td>{{ $postulante->grupo->nombre_grupo ?? 'Sin grupo' }}</td>
                                    <td class="fw-semibold">{{ $horario->asignacionDocente->materia ?? 'Sin materia' }}</td>
                                    <td>
                                        {{ $horario->asignacionDocente->docente->nombres ?? 'Sin docente' }}
                                        {{ $horario->asignacionDocente->docente->apellidos ?? '' }}
                                    </td>
                                    <td>{{ $horario->dia }}</td>
                                    <td>{{ substr($horario->hora_inicio, 0, 5) }}</td>
                                    <td>{{ substr($horario->hora_fin, 0, 5) }}</td>
                                    <td>{{ $horario->turno }}</td>
                                    <td>{{ $horario->modalidad }}</td>
                                    <td>{{ $horario->aula->codigo ?? 'Sin aula' }}</td>
                                    <td>{{ $horario->examen_presencial ? 'Sí' : 'No' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        Todavía no hay horarios registrados para tu grupo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($nota)
            @php
                $totalComputacion = $nota->computacion_1 + $nota->computacion_2 + $nota->computacion_3;
                $totalMatematicas = $nota->matematicas_1 + $nota->matematicas_2 + $nota->matematicas_3;
                $totalIngles = $nota->ingles_1 + $nota->ingles_2 + $nota->ingles_3;
                $totalFisica = $nota->fisica_1 + $nota->fisica_2 + $nota->fisica_3;
            @endphp

            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="cup-section-title">Notas registradas</h2>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Escala de evaluación:</strong>
                        Evaluación 1 sobre 30 puntos, evaluación 2 sobre 30 puntos y evaluación final sobre 40 puntos.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover text-center align-middle">
                            <thead>
                                <tr>
                                    <th>Materia</th>
                                    <th>Evaluación 1 /30</th>
                                    <th>Evaluación 2 /30</th>
                                    <th>Evaluación final /40</th>
                                    <th>Total materia /100</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td class="fw-semibold">Computación</td>
                                    <td>{{ $nota->computacion_1 }}</td>
                                    <td>{{ $nota->computacion_2 }}</td>
                                    <td>{{ $nota->computacion_3 }}</td>
                                    <td><strong>{{ number_format($totalComputacion, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Matemáticas</td>
                                    <td>{{ $nota->matematicas_1 }}</td>
                                    <td>{{ $nota->matematicas_2 }}</td>
                                    <td>{{ $nota->matematicas_3 }}</td>
                                    <td><strong>{{ number_format($totalMatematicas, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Inglés</td>
                                    <td>{{ $nota->ingles_1 }}</td>
                                    <td>{{ $nota->ingles_2 }}</td>
                                    <td>{{ $nota->ingles_3 }}</td>
                                    <td><strong>{{ number_format($totalIngles, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Física</td>
                                    <td>{{ $nota->fisica_1 }}</td>
                                    <td>{{ $nota->fisica_2 }}</td>
                                    <td>{{ $nota->fisica_3 }}</td>
                                    <td><strong>{{ number_format($totalFisica, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h2 class="cup-section-title mb-3">Resultado académico del CUP</h2>
                            <span class="cup-muted">Promedio final</span>
                            <div class="cup-result-score my-3">{{ number_format($nota->promedio, 2) }}</div>

                            @if($nota->estado == 'APROBADO')
                                <span class="badge bg-success fs-5 px-4 py-2">APROBADO</span>
                            @else
                                <span class="badge bg-danger fs-5 px-4 py-2">REPROBADO</span>
                            @endif

                            <p class="text-muted mt-4 mb-0">
                                Para aprobar el CUP, el postulante debe alcanzar mínimo 60 puntos en cada materia.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h2 class="cup-section-title mb-3">Resultado de admisión por cupo</h2>
                            <span class="cup-muted">Estado de admisión</span>

                            <div class="my-3">
                                @if($postulante->estado_admision == 'ADMITIDO')
                                    <span class="badge bg-success fs-5 px-4 py-2">ADMITIDO</span>
                                @elseif($postulante->estado_admision == 'NO_ADMITIDO')
                                    <span class="badge bg-danger fs-5 px-4 py-2">NO ADMITIDO</span>
                                @else
                                    <span class="badge bg-warning text-dark fs-5 px-4 py-2">PENDIENTE</span>
                                @endif
                            </div>

                            <p>
                                <strong>Carrera asignada:</strong>
                                {{ $postulante->carreraAsignada->nombre ?? 'Aún no asignada' }}
                            </p>

                            <p>
                                <strong>Tipo de asignación:</strong>

                                @if($postulante->tipo_asignacion == 'CARRERA_1')
                                    Primera opción de carrera
                                @elseif($postulante->tipo_asignacion == 'CARRERA_2')
                                    Segunda opción de carrera
                                @elseif($postulante->tipo_asignacion == 'SIN_CUPO')
                                    Sin cupo disponible
                                @elseif($postulante->tipo_asignacion == 'REPROBADO')
                                    No cumple nota mínima
                                @else
                                    Pendiente
                                @endif
                            </p>

                            <p class="text-muted mb-0">
                                {{ $postulante->observacion_admision ?? 'El proceso de admisión por cupos todavía no fue ejecutado.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <strong>Tus notas todavía no fueron registradas.</strong><br>
                Cuando el docente registre tus evaluaciones, aquí podrás ver tus notas, promedio final y estado de admisión.
            </div>
        @endif
    @endif
@endsection
