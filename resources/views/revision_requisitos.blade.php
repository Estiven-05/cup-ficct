@extends('layouts.app')

@section('title', 'Revisión de requisitos - CUP FICCT')
@section('page_kicker', 'Coordinación académica')
@section('page_title', 'Revisión de requisitos y pago')
@section('page_description', 'Valida la entrega documental y el pago digital registrado por los postulantes.')

@section('content')
@php
    $badgeClass = function ($estado) {
        return match ($estado) {
            'APROBADO' => 'bg-success',
            'RECHAZADO' => 'bg-danger',
            'EN_REVISION' => 'bg-warning text-dark',
            default => 'bg-secondary',
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
@endphp

@if(session('exito'))
    <div class="alert alert-success">
        {{ session('exito') }}
    </div>
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

<div class="card">
    <div class="card-header">
        <h2 class="cup-section-title">Postulantes enviados a revisión</h2>

    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle cup-review-table">
                <thead>
                    <tr>
                        <th>Postulante</th>
                        <th>Documentos</th>
                        <th>Pago</th>
                        <th>Estados</th>
                        <th>Fechas</th>
                        <th>Observación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($postulantes as $postulante)
                        @php
                            $documentosCompletos =
                                $postulante->doc_fotocopia_ci &&
                                $postulante->doc_titulo_bachiller &&
                                $postulante->doc_certificado_nacimiento &&
                                $postulante->doc_fotografia &&
                                $postulante->doc_formulario_inscripcion;

                            $pagoCompleto =
                                filled($postulante->metodo_pago) &&
                                filled($postulante->codigo_transaccion) &&
                                !is_null($postulante->monto_pago) &&
                                (float) $postulante->monto_pago > 0;

                            $archivosRequeridos = [
                                'archivo_fotocopia_ci' => 'CI',
                                'archivo_titulo_bachiller' => 'Título',
                                'archivo_certificado_nacimiento' => 'Nacimiento',
                                'archivo_fotografia' => 'Foto',
                                'archivo_formulario_inscripcion' => 'Formulario',
                            ];

                            $archivosCompletos = collect(array_keys($archivosRequeridos))
                                ->every(fn ($campo) => filled($postulante->{$campo}));

                            $puedeAprobar = $documentosCompletos && $archivosCompletos && $pagoCompleto;
                        @endphp

                        <tr>
                            <td>
                                <strong>#{{ $postulante->id }} - {{ $postulante->nombres }} {{ $postulante->apellidos }}</strong>
                                <br>
                                <small class="text-muted">CI: {{ $postulante->ci }}</small>
                                <br>
                                <small class="text-muted">Grupo: {{ $postulante->grupo->nombre_grupo ?? 'Sin grupo' }}</small>
                            </td>

                            <td>
                                <div class="d-flex flex-column gap-2" style="min-width: 250px;">
                                    @php
                                        $nombresArchivos = [
                                            'archivo_fotocopia_ci' => 'CI',
                                            'archivo_titulo_bachiller' => 'Título de bachiller',
                                            'archivo_certificado_nacimiento' => 'Certificado de nacimiento',
                                            'archivo_fotografia' => 'Fotografía',
                                            'archivo_formulario_inscripcion' => 'Formulario de inscripción',
                                        ];
                                    @endphp
                                    @foreach($nombresArchivos as $campo => $label)
                                        <div class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom border-light-subtle' : '' }}">
                                            <span class="text-secondary small fw-semibold">{{ $label }}</span>
                                            @if($postulante->{$campo})
                                                <a href="{{ Storage::url($postulante->{$campo}) }}" target="_blank" rel="noopener" class="badge cup-badge-aprobado text-decoration-none py-1 px-2 fw-bold" style="font-size: 0.7rem; border-radius: 4px;">
                                                    Ver archivo
                                                </a>
                                            @else
                                                <span class="badge cup-badge-pendiente py-1 px-2 fw-semibold" style="font-size: 0.7rem; border-radius: 4px;">
                                                    Sin archivo
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <td>
                                <div class="cup-payment-card" style="min-width: 250px;">
                                    <div class="cup-payment-row border-bottom border-light-subtle pb-2">
                                        <span class="cup-payment-label">Método</span>
                                        @if($postulante->metodo_pago)
                                            <span class="cup-payment-value">{{ $postulante->metodo_pago }}</span>
                                        @else
                                            <span class="text-muted small fw-medium">No registrado</span>
                                        @endif
                                    </div>
                                    <div class="cup-payment-row border-bottom border-light-subtle py-2">
                                        <span class="cup-payment-label">Código</span>
                                        @if($postulante->codigo_transaccion)
                                            <span class="cup-payment-code">{{ $postulante->codigo_transaccion }}</span>
                                        @else
                                            <span class="text-muted small fw-medium">No registrado</span>
                                        @endif
                                    </div>
                                    <div class="cup-payment-row border-bottom border-light-subtle py-2">
                                        <span class="cup-payment-label">Monto</span>
                                        @if($postulante->monto_pago)
                                            <span class="cup-payment-amount">
                                                Bs {{ number_format((float) $postulante->monto_pago, 2) }}
                                            </span>
                                        @else
                                            <span class="cup-payment-amount unregistered">
                                                No registrado
                                            </span>
                                        @endif
                                    </div>
                                    <div class="cup-payment-row pt-2">
                                        <span class="cup-payment-label">Fecha de pago</span>
                                        @if($postulante->fecha_pago)
                                            <span class="cup-payment-value text-secondary">
                                                {{ \Carbon\Carbon::parse($postulante->fecha_pago)->format('d/m/Y H:i') }}
                                            </span>
                                        @else
                                            <span class="badge cup-badge-pendiente py-1 px-2 fw-semibold" style="font-size: 0.7rem; border-radius: 4px;">
                                                No registrada
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Requisitos</small>
                                    <span class="badge {{ $badgeClass($postulante->estado_requisitos ?? 'PENDIENTE') }}">
                                        {{ $estadoTexto($postulante->estado_requisitos ?? 'PENDIENTE') }}
                                    </span>
                                </div>

                                <div>
                                    <small class="text-muted d-block">Pago</small>
                                    <span class="badge {{ $badgeClass($postulante->estado_pago_revision ?? 'PENDIENTE') }}">
                                        {{ $estadoTexto($postulante->estado_pago_revision ?? 'PENDIENTE') }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <small class="text-muted d-block">Envío</small>
                                <strong>
                                    @if($postulante->fecha_envio_requisitos)
                                        {{ \Carbon\Carbon::parse($postulante->fecha_envio_requisitos)->format('d/m/Y H:i') }}
                                    @else
                                        Sin enviar
                                    @endif
                                </strong>

                                <small class="text-muted d-block mt-2">Validación</small>
                                <strong>
                                    @if($postulante->fecha_validacion_requisitos)
                                        {{ \Carbon\Carbon::parse($postulante->fecha_validacion_requisitos)->format('d/m/Y H:i') }}
                                    @else
                                        Sin validar
                                    @endif
                                </strong>
                            </td>

                            <td>
                                {{ $postulante->observacion_requisitos ?? 'Sin observación.' }}
                            </td>

                            <td class="cup-actions-cell">
                                <form
                                    action="{{ route('requisitos.aprobar', $postulante->id) }}"
                                    method="POST"
                                    class="mb-2"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="btn btn-success btn-sm w-100"
                                        @disabled(!$puedeAprobar)
                                    >
                                        Aprobar requisitos y pago
                                    </button>

                                    @if(!$puedeAprobar)
                                        <small class="text-muted d-block mt-1">
                                            Faltan documentos, archivos o datos de pago.
                                        </small>
                                    @endif
                                </form>

                                <form
                                    action="{{ route('requisitos.rechazar', $postulante->id) }}"
                                    method="POST"
                                >
                                    @csrf

                                    <label class="form-label small mb-1">Observación para rechazo</label>
                                    <textarea
                                        name="observacion_requisitos"
                                        class="form-control form-control-sm mb-2"
                                        rows="2"
                                        minlength="5"
                                        maxlength="500"
                                        placeholder="Motivo del rechazo"
                                        required
                                    ></textarea>

                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        Rechazar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No existen postulantes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
