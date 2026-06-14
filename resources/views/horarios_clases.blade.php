@extends('layouts.app')

@section('title', 'Horarios de Clases - CUP FICCT')
@section('page_kicker', 'Programación')
@section('page_title', 'Horarios de clases')
@section('page_description', 'Organiza turnos, modalidad de clases, aulas y exámenes presenciales por grupo.')


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
            <h2 class="cup-section-title">Registrar horario de clase</h2>

        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('horarios.store') }}">
                @csrf

                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <label class="form-label">Asignación docente</label>
                        <select name="asignacion_docente_id" class="form-select" required>
                            <option value="">Seleccione una asignación</option>

                            @foreach($asignaciones as $asignacion)
                                <option value="{{ $asignacion->id }}" {{ old('asignacion_docente_id') == $asignacion->id ? 'selected' : '' }}>
                                    {{ $asignacion->docente->nombres }} {{ $asignacion->docente->apellidos }}
                                    - {{ $asignacion->grupo->nombre_grupo }}
                                    - {{ $asignacion->materia }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <label class="form-label">Modalidad</label>
                        <select name="modalidad" id="modalidad" class="form-select" required>
                            <option value="">Seleccione modalidad</option>
                            <option value="PRESENCIAL" {{ old('modalidad') == 'PRESENCIAL' ? 'selected' : '' }}>PRESENCIAL</option>
                            <option value="VIRTUAL" {{ old('modalidad') == 'VIRTUAL' ? 'selected' : '' }}>VIRTUAL</option>
                        </select>
                        <small class="text-muted">Si la clase es virtual, el aula puede quedar vacía.</small>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <label class="form-label">Aula</label>
                        <select name="aula_id" id="aula_id" class="form-select">
                            <option value="">Sin aula / clase virtual</option>

                            @foreach($aulas as $aula)
                                <option value="{{ $aula->id }}" {{ old('aula_id') == $aula->id ? 'selected' : '' }}>
                                    {{ $aula->codigo }}
                                    - {{ $aula->pabellon ?? 'Sin pabellón' }}
                                    - Capacidad: {{ $aula->capacidad }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Obligatoria para modalidad presencial.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Día</label>
                        <select name="dia" class="form-select" required>
                            <option value="">Seleccione un día</option>
                            <option value="Lunes" {{ old('dia') == 'Lunes' ? 'selected' : '' }}>Lunes</option>
                            <option value="Martes" {{ old('dia') == 'Martes' ? 'selected' : '' }}>Martes</option>
                            <option value="Miércoles" {{ old('dia') == 'Miércoles' ? 'selected' : '' }}>Miércoles</option>
                            <option value="Jueves" {{ old('dia') == 'Jueves' ? 'selected' : '' }}>Jueves</option>
                            <option value="Viernes" {{ old('dia') == 'Viernes' ? 'selected' : '' }}>Viernes</option>
                            <option value="Sábado" {{ old('dia') == 'Sábado' ? 'selected' : '' }}>Sábado</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Hora inicio</label>
                        <input type="time" name="hora_inicio" class="form-control" value="{{ old('hora_inicio') }}" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Hora fin</label>
                        <input type="time" name="hora_fin" class="form-control" value="{{ old('hora_fin') }}" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Turno</label>
                        <select name="turno" class="form-select" required>
                            <option value="">Seleccione turno</option>
                            <option value="MAÑANA" {{ old('turno') == 'MAÑANA' ? 'selected' : '' }}>MAÑANA</option>
                            <option value="TARDE" {{ old('turno') == 'TARDE' ? 'selected' : '' }}>TARDE</option>
                            <option value="NOCHE" {{ old('turno') == 'NOCHE' ? 'selected' : '' }}>NOCHE</option>
                        </select>
                    </div>
                </div>

                <div class="row align-items-end">
                    <div class="col-lg-4 mb-3">
                        <label class="cup-check-card">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="examen_presencial"
                                value="1"
                                {{ old('examen_presencial') ? 'checked' : '' }}
                            >
                            <span>
                                <strong>Examen presencial</strong>
                                <small class="text-muted d-block">Permite marcar examen presencial aunque la clase sea virtual.</small>
                            </span>
                        </label>
                    </div>

                    <div class="col-lg-8 mb-3">
                        <label class="form-label">Observación</label>
                        <textarea
                            name="observacion_horario"
                            class="form-control"
                            rows="2"
                            maxlength="500"
                            placeholder="Ej: examen presencial en laboratorio, enlace virtual pendiente, aula por confirmar..."
                        >{{ old('observacion_horario') }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    Registrar horario
                </button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h2 class="cup-section-title">Generar horario intensivo semanal</h2>
                <p class="cup-muted mb-0 mt-1">Crea bloques de lunes a viernes para las 4 materias del grupo.</p>
            </div>

            <form method="POST" action="{{ route('grupos.normalizar') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary">Normalizar grupos por turno</button>
            </form>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('horarios.intensivo') }}">
                @csrf

                <div class="row">
                    <div class="col-lg-3 mb-3">
                        <label class="form-label">Grupo</label>
                        <select name="grupo_id" class="form-select" required>
                            <option value="">Seleccione grupo</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}">{{ $grupo->nombre_grupo }} - {{ $grupo->turno ?? 'MAÑANA' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <label class="form-label">Turno</label>
                        <select name="turno" class="form-select" required>
                            <option value="MAÑANA">MAÑANA</option>
                            <option value="TARDE">TARDE</option>
                            <option value="NOCHE">NOCHE</option>
                        </select>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <label class="form-label">Modalidad</label>
                        <select name="modalidad" class="form-select" required>
                            <option value="PRESENCIAL">PRESENCIAL</option>
                            <option value="VIRTUAL">VIRTUAL</option>
                        </select>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <label class="form-label">Aula</label>
                        <select name="aula_id" class="form-select">
                            <option value="">Sin aula / virtual</option>
                            @foreach($aulas as $aula)
                                <option value="{{ $aula->id }}">{{ $aula->codigo }} - Capacidad: {{ $aula->capacidad }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row align-items-end">
                    <div class="col-lg-4 mb-3">
                        <label class="cup-check-card">
                            <input class="form-check-input" type="checkbox" name="examen_presencial" value="1">
                            <span>
                                <strong>Exámenes presenciales</strong>
                                <small class="text-muted d-block">Marca esta opción si los exámenes serán presenciales.</small>
                            </span>
                        </label>
                    </div>

                    <div class="col-lg-8 mb-3">
                        <label class="form-label">Observación</label>
                        <textarea name="observacion_horario" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Generar horario intensivo semanal</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="cup-section-title">Listado de horarios de clases</h2>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Docente</th>
                            <th>Materia</th>
                            <th>Grupo</th>
                            <th>Aula</th>
                            <th>Día</th>
                            <th>Hora inicio</th>
                            <th>Hora fin</th>
                            <th>Turno</th>
                            <th>Modalidad</th>
                            <th>Examen presencial</th>
                            <th>Observación</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($horarios as $horario)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $horario->asignacionDocente->docente->nombres ?? 'Sin docente' }}
                                    {{ $horario->asignacionDocente->docente->apellidos ?? '' }}
                                </td>
                                <td>{{ $horario->asignacionDocente->materia ?? 'Sin materia' }}</td>
                                <td>{{ $horario->asignacionDocente->grupo->nombre_grupo ?? 'Sin grupo' }}</td>
                                <td>{{ $horario->aula->codigo ?? 'Sin aula' }}</td>
                                <td>{{ $horario->dia }}</td>
                                <td>{{ substr($horario->hora_inicio, 0, 5) }}</td>
                                <td>{{ substr($horario->hora_fin, 0, 5) }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $horario->turno ?? 'MAÑANA' }}</span>
                                </td>
                                <td>
                                    @if(($horario->modalidad ?? 'PRESENCIAL') === 'VIRTUAL')
                                        <span class="badge bg-primary">VIRTUAL</span>
                                    @else
                                        <span class="badge bg-success">PRESENCIAL</span>
                                    @endif
                                </td>
                                <td>
                                    @if($horario->examen_presencial)
                                        <span class="badge bg-success">Sí</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $horario->observacion_horario ?? 'Sin observación' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    Todavía no hay horarios de clases registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const modalidadSelect = document.getElementById('modalidad');
    const aulaSelect = document.getElementById('aula_id');

    function syncAulaRequirement() {
        aulaSelect.required = modalidadSelect.value === 'PRESENCIAL';
    }

    modalidadSelect.addEventListener('change', syncAulaRequirement);
    syncAulaRequirement();
</script>
@endpush
