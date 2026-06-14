@extends('layouts.app')

@section('title', 'Mis requisitos y pago - CUP FICCT')
@section('page_kicker', 'Postulante')
@section('page_title', 'Mis requisitos y pago')

@section('content')
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

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h2 class="cup-section-title">Estado actual</h2>
            </div>

            <div class="card-body">
                <div class="cup-info-item mb-3">
                    <span>Postulante</span>
                    <strong>{{ $postulante->nombres }} {{ $postulante->apellidos }}</strong>
                </div>

                <div class="cup-info-item mb-3">
                    <span>CI</span>
                    <strong>{{ $postulante->ci }}</strong>
                </div>

                <div class="cup-info-item mb-3">
                    <span>Estado de requisitos</span>
                    <strong>
                        <span class="badge {{ $badgeClass($postulante->estado_requisitos ?? 'PENDIENTE') }}">
                            {{ $estadoTexto($postulante->estado_requisitos ?? 'PENDIENTE') }}
                        </span>
                    </strong>
                </div>

                <div class="cup-info-item mb-3">
                    <span>Estado del pago</span>
                    <strong>
                        <span class="badge {{ $badgeClass($postulante->estado_pago_revision ?? 'PENDIENTE') }}">
                            {{ $estadoTexto($postulante->estado_pago_revision ?? 'PENDIENTE') }}
                        </span>
                    </strong>
                </div>

                <div class="cup-status-note">
                    <strong>Seguimiento</strong>
                    <p class="text-muted mb-1 mt-2">
                        @if($postulante->fecha_envio_requisitos)
                            Enviado el {{ \Carbon\Carbon::parse($postulante->fecha_envio_requisitos)->format('d/m/Y H:i') }}.
                        @else
                            Aún no enviaste tus requisitos a revisión.
                        @endif
                    </p>

                    @if($postulante->fecha_validacion_requisitos)
                        <p class="text-muted mb-0">
                            Validado el {{ \Carbon\Carbon::parse($postulante->fecha_validacion_requisitos)->format('d/m/Y H:i') }}.
                        </p>
                    @endif
                </div>

                @if($postulante->observacion_requisitos)
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Observación del coordinador:</strong><br>
                        {{ $postulante->observacion_requisitos }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h2 class="cup-section-title">Documentos y pago</h2>
                <p class="cup-muted mb-0">Los checkboxes confirman la entrega documental para revisión.</p>
            </div>

            <div class="card-body">
                <form action="{{ route('requisitos.guardar') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h3 class="cup-section-title mb-3">Documentos requeridos</h3>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="cup-check-card">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="doc_fotocopia_ci"
                                    value="1"
                                    {{ old('doc_fotocopia_ci', $postulante->doc_fotocopia_ci) ? 'checked' : '' }}
                                >
                                <span>
                                    <strong>Fotocopia de CI</strong>
                                    <small class="text-muted d-block">Documento de identidad vigente.</small>
                                </span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="cup-check-card">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="doc_titulo_bachiller"
                                    value="1"
                                    {{ old('doc_titulo_bachiller', $postulante->doc_titulo_bachiller) ? 'checked' : '' }}
                                >
                                <span>
                                    <strong>Título de bachiller</strong>
                                    <small class="text-muted d-block">Requisito obligatorio del proceso.</small>
                                </span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="cup-check-card">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="doc_certificado_nacimiento"
                                    value="1"
                                    {{ old('doc_certificado_nacimiento', $postulante->doc_certificado_nacimiento) ? 'checked' : '' }}
                                >
                                <span>
                                    <strong>Certificado de nacimiento</strong>
                                    <small class="text-muted d-block">Registro personal actualizado.</small>
                                </span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="cup-check-card">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="doc_fotografia"
                                    value="1"
                                    {{ old('doc_fotografia', $postulante->doc_fotografia) ? 'checked' : '' }}
                                >
                                <span>
                                    <strong>Fotografía actual</strong>
                                    <small class="text-muted d-block">Fotografía reciente del postulante.</small>
                                </span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="cup-check-card">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="doc_formulario_inscripcion"
                                    value="1"
                                    {{ old('doc_formulario_inscripcion', $postulante->doc_formulario_inscripcion) ? 'checked' : '' }}
                                >
                                <span>
                                    <strong>Formulario de inscripción</strong>
                                    <small class="text-muted d-block">Comprobante del registro inicial.</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="cup-form-section">
                        <h3 class="cup-section-title mb-3">Archivos respaldatorios</h3>
                        <p class="cup-muted">Formatos aceptados: PDF, JPG, JPEG o PNG. Tamaño máximo: 5 MB por archivo.</p>

                        @php
                            $archivosPostulante = [
                                'archivo_fotocopia_ci' => 'Fotocopia de CI',
                                'archivo_titulo_bachiller' => 'Título de bachiller',
                                'archivo_certificado_nacimiento' => 'Certificado de nacimiento',
                                'archivo_fotografia' => 'Fotografía actual',
                                'archivo_formulario_inscripcion' => 'Formulario de inscripción',
                            ];
                        @endphp

                        <div class="row g-3">
                            @foreach($archivosPostulante as $campo => $label)
                                <div class="col-md-6">
                                    <label class="form-label">{{ $label }}</label>
                                    <input type="file" name="{{ $campo }}" class="form-control" accept=".pdf,.jpg,.jpeg,.png">

                                    @if($postulante->{$campo})
                                        <small class="d-block mt-1">
                                            <span class="badge bg-success">Archivo cargado</span>
                                            <a href="{{ Storage::url($postulante->{$campo}) }}" target="_blank" rel="noopener">Ver/descargar</a>
                                        </small>
                                    @else
                                        <small class="text-muted">Archivo pendiente.</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="cup-form-section">
                        <h3 class="cup-section-title mb-3">Pasarela de pago CUP</h3>

                         @if($postulante->codigo_transaccion)
                            <div class="card border-success mb-3">
                                <div class="card-body">
                                    <h4 class="cup-section-title text-success">Comprobante visual de pago</h4>
                                    <div class="cup-info-grid mt-3">
                                        <div class="cup-info-item"><span>Método</span><strong>{{ $postulante->metodo_pago }}</strong></div>
                                        <div class="cup-info-item"><span>Código</span><strong>{{ $postulante->codigo_transaccion }}</strong></div>
                                        <div class="cup-info-item"><span>Monto</span><strong>Bs {{ number_format((float) $postulante->monto_pago, 2) }}</strong></div>
                                        <div class="cup-info-item"><span>Fecha/hora</span><strong>{{ $postulante->fecha_pago ? \Carbon\Carbon::parse($postulante->fecha_pago)->format('d/m/Y H:i') : 'Pendiente' }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Método de pago</label>
                                <select name="metodo_pago" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach(['Tarjeta', 'PayPal', 'Transferencia bancaria', 'Billetera digital'] as $metodo)
                                        <option value="{{ $metodo }}" {{ old('metodo_pago', $postulante->metodo_pago) == $metodo ? 'selected' : '' }}>{{ $metodo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Código de transacción</label>
                                <input type="text" name="codigo_transaccion" class="form-control" value="{{ old('codigo_transaccion', $codigoTransaccionSugerido ?? $postulante->codigo_transaccion) }}" readonly>
                                <small class="text-muted">Generado automáticamente por el sistema.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Monto CUP</label>
                                <input type="number" name="monto_pago" class="form-control" min="1" step="0.01" value="{{ old('monto_pago', $postulante->monto_pago ?? ($montoCup ?? 150)) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                        <button type="submit" class="btn btn-primary">
                            Enviar requisitos a revisión
                        </button>

                        <a href="{{ url('/mi-resultado') }}" class="btn btn-outline-secondary">
                            Volver a mi resultado
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
