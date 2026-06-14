@extends('layouts.app')

@section('title', 'Login - Sistema CUP FICCT')
@section('body_class', 'cup-auth')

@section('auth')
<div class="cup-auth-card">
    <div class="card border-0 overflow-hidden">
        <div class="cup-auth-hero">
            <span class="cup-kicker text-white-50">Sistema CUP FICCT</span>
            <h1 class="mt-2 mb-2">Inicio de sesión</h1>
            <p class="mb-0 text-white-50">Accede al panel académico de admisión universitaria.</p>
        </div>

        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if(session('login_lock_seconds'))
                <div class="alert alert-warning">
                    Cuenta bloqueada temporalmente. Tiempo aproximado restante:
                    <strong>{{ max(1, ceil(session('login_lock_seconds') / 60)) }} minutos</strong>.
                </div>
            @endif

            <form method="POST" action="{{ route('login.procesar') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        placeholder="Ingrese su correo"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Ingrese su contraseña"
                            required
                        >
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password>Mostrar</button>
                    </div>
                </div>

                <a href="{{ route('password.recuperar') }}" class="d-inline-block mb-3">¿Olvidaste tu contraseña?</a>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                    Iniciar sesión
                </button>
            </form>

            <div class="cup-form-section">
                <p class="text-center text-muted small fw-bold mb-3">¿No tienes cuenta?</p>
                <div class="d-grid gap-2">
                    <a href="{{ url('/inscripcion') }}" class="btn btn-outline-primary py-2 fw-bold">
                        Registrarse como postulante
                    </a>
                    <a href="{{ route('solicitud-docente') }}" class="btn btn-light text-secondary border py-2 small fw-semibold">
                        Solicitar cuenta docente
                    </a>
                </div>
            </div>

            <div class="mt-4 p-3 bg-light rounded border text-center small text-muted shadow-sm">
                <strong class="text-dark">Usuario de prueba:</strong><br>
                Admin: admin@ficct.uagrm.edu<br>
                Contraseña: password123
            </div>
        </div>
    </div>
</div>
@endsection
