@extends('layouts.app')

@section('title', 'Gestión de Docentes - CUP FICCT')
@section('page_kicker', 'Administración académica')
@section('page_title', 'Gestión de docentes')
@section('page_description', 'Registro, documentos y competencias docentes por materia.')


@section('content')

<div class="card mb-4">
    <div class="card-header">
        <h2 class="cup-section-title">Resumen docente por módulo</h2>
        <p class="cup-muted mb-0">Estado documental, competencias y disponibilidad para asignación.</p>
    </div>
    <div class="card-body">
        <div class="cup-stat-grid">
            <div class="card cup-stat"><span>Total docentes</span><strong>{{ $docentes->count() }}</strong><small>Registrados</small></div>
            <div class="card cup-stat success"><span>Habilitados</span><strong>{{ $docentes->where('estado', 'HABILITADO')->count() }}</strong><small>Listos para asignación</small></div>
            <div class="card cup-stat warning"><span>Docs en revisión</span><strong>{{ $docentes->where('estado_documentos_docente', 'EN_REVISION')->count() }}</strong><small>Validación pendiente</small></div>
            <div class="card cup-stat success"><span>Competencias aprobadas</span><strong>{{ $docentes->flatMap->competencias->where('estado', 'APROBADO')->count() }}</strong><small>Por materia</small></div>
        </div>
    </div>
</div>
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
            <h2 class="cup-section-title">Registrar docente</h2>
            <p class="cup-muted mb-0 mt-1">Completa datos personales y requisitos académicos generales.</p>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('docentes.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">CI</label>
                        <input type="text" name="ci" class="form-control" value="{{ old('ci') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="nombres" class="form-control" value="{{ old('nombres') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos') }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" value="{{ old('correo') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Profesión</label>
                        <input type="text" name="profesion" class="form-control" value="{{ old('profesion') }}" placeholder="Ej: Ingeniero de Sistemas" required>
                    </div>
                </div>

                <div class="cup-form-section">
                    <h2 class="cup-section-title mb-3">Requisitos académicos generales</h2>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="es_profesional" id="es_profesional">
                        <label class="form-check-label" for="es_profesional">Cuenta con título profesional</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="tiene_maestria" id="tiene_maestria">
                        <label class="form-check-label" for="tiene_maestria">Cuenta con maestría</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="tiene_diplomado" id="tiene_diplomado">
                        <label class="form-check-label" for="tiene_diplomado">Cuenta con diplomado en educación superior</label>
                    </div>

                    <button type="submit" class="btn btn-success">Registrar docente</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="cup-section-title">Listado, documentos y competencias</h2>
        </div>

        <div class="card-body">
            <div class="accordion" id="docentesAccordion">
                @forelse($docentes as $docente)
                    @php
                        $documentos = [
                            'archivo_titulo_profesional' => 'Título profesional',
                            'archivo_curriculum' => 'Currículum vitae',
                            'archivo_experiencia_docente' => 'Experiencia docente',
                            'archivo_certificado_capacitacion' => 'Capacitación por materia',
                            'archivo_certificado_idioma' => 'Certificado de idioma',
                            'archivo_otro_respaldo' => 'Otro respaldo',
                        ];
                    @endphp

                    <div class="accordion-item mb-3 rounded-3 overflow-hidden shadow-sm" style="border: 1px solid var(--cup-border);">
                        <h2 class="accordion-header" id="headingDocente{{ $docente->id }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocente{{ $docente->id }}">
                                #{{ $docente->id }} - {{ $docente->nombres }} {{ $docente->apellidos }}
                                <span class="ms-2 badge {{ $docente->estado === 'HABILITADO' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $docente->estado }}</span>
                                <span class="ms-2 badge bg-secondary">{{ $docente->estado_documentos_docente ?? 'PENDIENTE' }}</span>
                            </button>
                        </h2>

                        <div id="collapseDocente{{ $docente->id }}" class="accordion-collapse collapse" data-bs-parent="#docentesAccordion">
                            <div class="accordion-body">
                                <div class="cup-info-grid mb-4">
                                    <div class="cup-info-item"><span>CI</span><strong>{{ $docente->ci }}</strong></div>
                                    <div class="cup-info-item"><span>Correo</span><strong>{{ $docente->correo ?? 'Sin correo' }}</strong></div>
                                    <div class="cup-info-item"><span>Profesión</span><strong>{{ $docente->profesion }}</strong></div>
                                    <div class="cup-info-item"><span>Teléfono</span><strong>{{ $docente->telefono ?? 'Sin teléfono' }}</strong></div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <div class="card p-4 bg-white border border-light-subtle h-100 shadow-sm">
                                            <h3 class="cup-section-title mb-4 pb-2 border-bottom border-light-subtle text-primary">
                                                Documentos reales del docente
                                            </h3>

                                            <form method="POST" action="{{ route('docentes.documentos.guardar', $docente) }}" enctype="multipart/form-data">
                                                @csrf

                                                <div class="row g-3">
                                                    @foreach($documentos as $campo => $label)
                                                        <div class="col-md-6 mb-2">
                                                            <label class="form-label small fw-bold mb-1 text-secondary">{{ $label }}</label>
                                                            <input type="file" name="{{ $campo }}" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                                            @if($docente->{$campo})
                                                                <small class="d-flex align-items-center gap-2 mt-1">
                                                                    <span class="badge cup-badge-aprobado text-decoration-none py-1 px-2 fw-bold" style="font-size: 0.7rem; border-radius: 4px;">Cargado</span>
                                                                    <a href="{{ Storage::url($docente->{$campo}) }}" target="_blank" rel="noopener" class="text-primary fw-semibold text-decoration-underline" style="font-size: 0.85rem;">Ver archivo</a>
                                                                </small>
                                                            @else
                                                                <small class="text-muted d-block mt-1">
                                                                    <span class="badge cup-badge-pendiente py-1 px-2 fw-semibold" style="font-size: 0.7rem; border-radius: 4px;">Pendiente</span>
                                                                </small>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="mt-4 pt-3 border-top border-light-subtle">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">Guardar documentos</button>
                                                </div>
                                            </form>

                                            <div class="mt-4 pt-3 border-top border-light-subtle">
                                                <h4 class="small fw-bold text-secondary text-uppercase mb-3">Acciones de validación (Coordinación)</h4>
                                                <div class="d-flex flex-column gap-3">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <form method="POST" action="{{ route('docentes.documentos.aprobar', $docente) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm">Aprobar documentos</button>
                                                        </form>
                                                    </div>

                                                    <form method="POST" action="{{ route('docentes.documentos.rechazar', $docente) }}" class="d-flex flex-column gap-2 mt-1">
                                                        @csrf
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" name="observacion_documentos_docente" class="form-control" placeholder="Observación de rechazo (obligatoria)" required>
                                                            <button type="submit" class="btn btn-outline-danger btn-sm">Rechazar documentos</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            @if($docente->observacion_documentos_docente)
                                                <div class="alert alert-info mt-3 mb-0 py-2 px-3 small">
                                                    <strong>Observación actual:</strong> {{ $docente->observacion_documentos_docente }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="card p-4 bg-white border border-light-subtle h-100 shadow-sm">
                                            <h3 class="cup-section-title mb-4 pb-2 border-bottom border-light-subtle text-primary">
                                                Competencias por materia
                                            </h3>

                                            <div class="bg-light-subtle border border-light-subtle rounded-3 p-3 mb-4">
                                                <h4 class="small fw-bold text-secondary text-uppercase mb-3">Registrar nueva competencia</h4>
                                                <form method="POST" action="{{ route('docentes.competencias.guardar', $docente) }}" enctype="multipart/form-data">
                                                    @csrf

                                                    <div class="row g-3">
                                                        <div class="col-md-6 mb-2">
                                                            <label class="form-label small fw-bold mb-1 text-secondary">Materia</label>
                                                            <select name="materia" class="form-select form-select-sm" required>
                                                                <option value="">Seleccione</option>
                                                                <option value="Computación">Computación</option>
                                                                <option value="Matemáticas">Matemáticas</option>
                                                                <option value="Inglés">Inglés</option>
                                                                <option value="Física">Física</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label class="form-label small fw-bold mb-1 text-secondary">Tipo de respaldo</label>
                                                            <select name="tipo_respaldo" class="form-select form-select-sm" required>
                                                                <option value="TITULO">Título</option>
                                                                <option value="EXPERIENCIA">Experiencia</option>
                                                                <option value="CAPACITACION">Capacitación</option>
                                                                <option value="CERTIFICACION">Certificación</option>
                                                                <option value="OTRO">Otro</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12 mb-2">
                                                            <label class="form-label small fw-bold mb-1 text-secondary">Descripción</label>
                                                            <textarea name="descripcion" class="form-control form-control-sm" rows="2" placeholder="Detalle el respaldo..."></textarea>
                                                        </div>
                                                        <div class="col-12 mb-2">
                                                            <label class="form-label small fw-bold mb-1 text-secondary">Archivo de respaldo</label>
                                                            <input type="file" name="archivo_respaldo" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                                        </div>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary btn-sm mt-3">Registrar competencia</button>
                                                </form>
                                            </div>

                                            <div class="border border-light-subtle rounded-3 p-3 bg-white">
                                                <h4 class="small fw-bold text-secondary text-uppercase mb-3">Listado de competencias</h4>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover align-middle mb-0">
                                                        <thead>
                                                            <tr class="table-light">
                                                                <th class="small py-2">Materia</th>
                                                                <th class="small py-2">Respaldo</th>
                                                                <th class="small py-2">Estado</th>
                                                                <th class="small py-2 text-end">Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($docente->competencias as $competencia)
                                                                <tr>
                                                                    <td class="fw-semibold small">{{ $competencia->materia }}</td>
                                                                    <td class="small">
                                                                        <span class="text-secondary fw-semibold">{{ $competencia->tipo_respaldo }}</span>
                                                                        @if($competencia->archivo_respaldo)
                                                                            <br>
                                                                            <a href="{{ Storage::url($competencia->archivo_respaldo) }}" target="_blank" rel="noopener" class="text-primary fw-medium text-decoration-underline" style="font-size: 0.8rem;">ver archivo</a>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($competencia->estado === 'APROBADO')
                                                                            <span class="badge cup-badge-aprobado py-1 px-2 fw-semibold" style="font-size: 0.7rem; border-radius: 4px;">APROBADO</span>
                                                                        @elseif($competencia->estado === 'RECHAZADO')
                                                                            <span class="badge cup-badge-rechazado py-1 px-2 fw-semibold" style="font-size: 0.7rem; border-radius: 4px;">RECHAZADO</span>
                                                                        @else
                                                                            <span class="badge cup-badge-revision py-1 px-2 fw-semibold" style="font-size: 0.7rem; border-radius: 4px;">{{ $competencia->estado }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <div class="d-flex flex-column gap-1 align-items-end">
                                                                            <form method="POST" action="{{ route('docentes.competencias.aprobar', $competencia) }}" class="d-inline">
                                                                                @csrf
                                                                                <button class="btn btn-success btn-sm py-1 px-2" style="font-size: 0.75rem; font-weight: bold;" type="submit">Aprobar</button>
                                                                            </form>
                                                                            <form method="POST" action="{{ route('docentes.competencias.rechazar', $competencia) }}" class="d-flex gap-1 mt-1 justify-content-end align-items-center">
                                                                                @csrf
                                                                                <input type="text" name="observacion" class="form-control form-control-sm py-1 px-2" style="font-size: 0.75rem; max-width: 110px;" placeholder="Motivo" required>
                                                                                <button class="btn btn-outline-danger btn-sm py-1 px-2" style="font-size: 0.75rem; font-weight: bold;" type="submit">Rechazar</button>
                                                                            </form>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="4" class="text-center text-muted py-3 small">Sin competencias registradas.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">Todavía no hay docentes registrados.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
