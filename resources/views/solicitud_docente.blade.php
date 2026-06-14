@extends('layouts.app')

@section('title', 'Solicitud de cuenta docente - CUP FICCT')
@section('body_class', 'cup-auth')

@section('auth')
<div class="cup-auth-card">
    <div class="card border-0 overflow-hidden shadow-lg">
        <div class="cup-auth-hero bg-dark text-white p-4 text-center">
            <span class="cup-kicker text-white-50">Postulación Académica</span>
            <h1 class="mt-2 mb-2 h3">Solicitud de Cuenta Docente</h1>
            <p class="mb-0 text-white-50 small">Registra tus datos profesionales para postularte como docente en el sistema CUP.</p>
        </div>

        <div class="card-body p-4">
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                <div class="small">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Nota:</strong> La cuenta docente será revisada y habilitada por la administración académica.
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('solicitud-docente.procesar') }}" class="needs-validation">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Cédula de Identidad (CI)</label>
                    <input 
                        type="text" 
                        name="ci" 
                        class="form-control" 
                        value="{{ old('ci') }}" 
                        required 
                        placeholder="Ej: 8765432"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombres</label>
                    <input 
                        type="text" 
                        name="nombres" 
                        class="form-control" 
                        value="{{ old('nombres') }}" 
                        required 
                        placeholder="Ej: Carlos"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Apellidos</label>
                    <input 
                        type="text" 
                        name="apellidos" 
                        class="form-control" 
                        value="{{ old('apellidos') }}" 
                        required 
                        placeholder="Ej: Justiniano Méndez"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input 
                        type="email" 
                        name="correo" 
                        class="form-control" 
                        value="{{ old('correo') }}" 
                        placeholder="Ej: docente@ejemplo.com"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Teléfono / Celular</label>
                    <input 
                        type="text" 
                        name="telefono" 
                        class="form-control" 
                        value="{{ old('telefono') }}" 
                        placeholder="Ej: 78901234"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Profesión / Especialidad</label>
                    <input 
                        type="text" 
                        name="profesion" 
                        class="form-control" 
                        value="{{ old('profesion') }}" 
                        required 
                        placeholder="Ej: Lic. en Ciencias de la Computación"
                    >
                </div>

                <div class="mt-4 pt-2 border-top">
                    <button type="submit" class="btn btn-primary w-100 mb-2 py-2 fw-bold">
                        Enviar Solicitud
                    </button>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 py-2">
                        Volver al login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
