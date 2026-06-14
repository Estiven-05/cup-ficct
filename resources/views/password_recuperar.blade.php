@extends('layouts.app')

@section('title', 'Recuperar contraseña - CUP FICCT')
@section('body_class', 'cup-auth')

@section('auth')
<div class="cup-auth-card">
    <div class="card border-0 overflow-hidden">
        <div class="cup-auth-hero">
            <span class="cup-kicker text-white-50">Seguridad y usuarios</span>
            <h1 class="mt-2 mb-2">Recuperar contraseña</h1>
            <p class="mb-0 text-white-50">Genera un enlace temporal para restablecer tu acceso.</p>
        </div>

        <div class="card-body p-4">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if(session('reset_url'))
                <div class="alert alert-info">
                    <strong>Enlace académico de prueba:</strong><br>
                    <a href="{{ session('reset_url') }}">{{ session('reset_url') }}</a>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.recuperar.procesar') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100">Generar enlace de recuperación</button>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 mt-2">Volver al login</a>
            </form>
        </div>
    </div>
</div>
@endsection