@extends('layouts.app')

@section('title', 'Panel docente - CUP FICCT')
@section('page_kicker', 'Docentes y carga horaria')
@section('page_title', 'Panel docente')
@section('page_description', 'Consulta carga académica, horarios y asistencia docente.')


@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

    @if(!$docenteActual && !$esAdmin)
        <div class="alert alert-warning">
            No se encontró un registro de docente vinculado a este usuario. Verifica el correo del docente o la importación de usuarios.
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="cup-section-title">Carga académica</h2>
            <p class="cup-muted mb-0">Materias, grupos, modalidad, aulas y horarios asignados.</p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            @if($esAdmin)
                                <th>Docente</th>
                            @endif
                            <th>Materia</th>
                            <th>Grupo</th>
                            <th>Postulantes</th>
                            <th>Día</th>
                            <th>Horario</th>
                            <th>Turno</th>
                            <th>Modalidad</th>
                            <th>Aula</th>
                            <th>Examen presencial</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                            @forelse($asignacion->horarios as $horario)
                                <tr>
                                    @if($esAdmin)
                                        <td>{{ $asignacion->docente->nombres ?? '' }} {{ $asignacion->docente->apellidos ?? '' }}</td>
                                    @endif
                                    <td class="fw-semibold">{{ $asignacion->materia }}</td>
                                    <td>{{ $asignacion->grupo->nombre_grupo ?? 'Sin grupo' }}</td>
                                    <td>
                                        @php $postulantesCount = $asignacion->grupo ? $asignacion->grupo->postulantes->count() : 0; @endphp
                                        @if($postulantesCount > 0)
                                            <span class="badge cup-badge-aprobado fw-semibold" style="font-size: 0.8rem;">
                                                {{ $postulantesCount }} {{ $postulantesCount === 1 ? 'postulante' : 'postulantes' }}
                                            </span>
                                        @else
                                            <span class="badge cup-badge-pendiente fw-semibold text-muted" style="font-size: 0.8rem;">
                                                Sin postulantes
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($horario->dia)
                                            <span class="badge cup-badge-aprobado fw-semibold" style="font-size: 0.8rem;">
                                                {{ $horario->dia }}
                                            </span>
                                        @else
                                            <span class="badge cup-badge-pendiente fw-semibold text-muted" style="font-size: 0.8rem;">
                                                Sin día
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($horario->hora_inicio && $horario->hora_fin)
                                            <span class="badge bg-light text-dark border border-secondary-subtle font-monospace fw-bold" style="font-size: 0.8rem;">
                                                {{ substr($horario->hora_inicio, 0, 5) }} - {{ substr($horario->hora_fin, 0, 5) }}
                                            </span>
                                        @else
                                            <span class="badge cup-badge-pendiente fw-semibold text-muted" style="font-size: 0.8rem;">
                                                Sin horario
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $horario->turno }}</td>
                                    <td>{{ $horario->modalidad }}</td>
                                    <td>{{ $horario->aula->codigo ?? 'Sin aula' }}</td>
                                    <td>{{ $horario->examen_presencial ? 'Sí' : 'No' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    @if($esAdmin)
                                        <td>{{ $asignacion->docente->nombres ?? '' }} {{ $asignacion->docente->apellidos ?? '' }}</td>
                                    @endif
                                    <td class="fw-semibold">{{ $asignacion->materia }}</td>
                                    <td>{{ $asignacion->grupo->nombre_grupo ?? 'Sin grupo' }}</td>
                                    <td>
                                        @php $postulantesCount = $asignacion->grupo ? $asignacion->grupo->postulantes->count() : 0; @endphp
                                        @if($postulantesCount > 0)
                                            <span class="badge cup-badge-aprobado fw-semibold" style="font-size: 0.8rem;">
                                                {{ $postulantesCount }} {{ $postulantesCount === 1 ? 'postulante' : 'postulantes' }}
                                            </span>
                                        @else
                                            <span class="badge cup-badge-pendiente fw-semibold text-muted" style="font-size: 0.8rem;">
                                                Sin postulantes
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge cup-badge-pendiente fw-semibold text-muted" style="font-size: 0.8rem;">
                                            Sin día
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge cup-badge-pendiente fw-semibold text-muted" style="font-size: 0.8rem;">
                                            Sin horario
                                        </span>
                                    </td>
                                    <td class="text-muted">—</td>
                                    <td class="text-muted">—</td>
                                    <td class="text-muted">—</td>
                                    <td class="text-muted">—</td>
                                </tr>
                            @endforelse
                        @empty
                            <tr>
                                <td colspan="{{ $esAdmin ? 10 : 9 }}" class="text-center text-muted py-4">
                                    No hay asignaciones activas para mostrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(!$esAdmin && $docenteActual)
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="cup-section-title">Mis documentos docentes</h2>
                <p class="cup-muted mb-0">Carga respaldos en PDF, JPG, JPEG o PNG. Coordinación hará la validación final.</p>
            </div>
            <div class="card-body">
                @php
                    $documentosDocente = [
                        'archivo_titulo_profesional' => 'Título profesional',
                        'archivo_curriculum' => 'Currículum vitae',
                        'archivo_experiencia_docente' => 'Experiencia docente',
                        'archivo_certificado_capacitacion' => 'Capacitación por materia',
                        'archivo_certificado_idioma' => 'Certificado de idioma',
                        'archivo_otro_respaldo' => 'Otro respaldo',
                    ];
                @endphp

                <form method="POST" action="{{ route('docente.documentos.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        @foreach($documentosDocente as $campo => $label)
                            <div class="col-md-6">
                                <label class="form-label">{{ $label }}</label>
                                <input type="file" name="{{ $campo }}" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                @if($docenteActual->{$campo})
                                    <small class="d-block mt-1">
                                        <span class="badge bg-success">Cargado</span>
                                        <a href="{{ Storage::url($docenteActual->{$campo}) }}" target="_blank" rel="noopener">ver</a>
                                    </small>
                                @else
                                    <small class="text-muted">Pendiente</small>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-outline-primary mt-3">Enviar documentos a revisión</button>
                </form>

                <div class="cup-status-note mt-3">
                    <span>Estado documental</span>
                    <strong>{{ $docenteActual->estado_documentos_docente ?? 'PENDIENTE' }}</strong>
                    <p class="mb-0 text-muted">{{ $docenteActual->observacion_documentos_docente ?? 'Sin observación registrada.' }}</p>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="cup-section-title">Registrar asistencia docente</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('docente.asistencia.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <label class="form-label">Asignación</label>
                            <select name="asignacion_docente_id" class="form-select" required>
                                <option value="">Seleccione asignación</option>
                                @foreach($asignaciones as $asignacion)
                                    <option value="{{ $asignacion->id }}">
                                        {{ $asignacion->materia }} - {{ $asignacion->grupo->nombre_grupo ?? 'Sin grupo' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-lg-3 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select" required>
                                <option value="PRESENTE">PRESENTE</option>
                                <option value="AUSENTE">AUSENTE</option>
                                <option value="LICENCIA">LICENCIA</option>
                            </select>
                        </div>

                        <div class="col-lg-12 mb-3">
                            <label class="form-label">Observación</label>
                            <textarea name="observacion" class="form-control" rows="2" maxlength="500"></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Guardar asistencia</button>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="cup-section-title">Últimos registros de asistencia</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            @if($esAdmin)
                                <th>Docente</th>
                            @endif
                            <th>Fecha</th>
                            <th>Asignación</th>
                            <th>Estado</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asistencias as $asistencia)
                            <tr>
                                @if($esAdmin)
                                    <td>{{ $asistencia->docente->nombres ?? '' }} {{ $asistencia->docente->apellidos ?? '' }}</td>
                                @endif
                                <td>{{ optional($asistencia->fecha)->format('d/m/Y') }}</td>
                                <td>{{ $asistencia->asignacionDocente->materia ?? 'Sin materia' }} - {{ $asistencia->asignacionDocente->grupo->nombre_grupo ?? 'Sin grupo' }}</td>
                                <td><span class="badge bg-primary">{{ $asistencia->estado }}</span></td>
                                <td>{{ $asistencia->observacion ?? 'Sin observación' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $esAdmin ? 5 : 4 }}" class="text-center text-muted py-4">
                                    No hay asistencias registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
