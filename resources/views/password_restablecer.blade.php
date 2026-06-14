@extends('layouts.app')

@section('title', 'Restablecer contraseña - CUP FICCT')
@section('body_class', 'cup-auth')

@section('auth')
<div class="cup-auth-card">
    <div class="card border-0 overflow-hidden">
        <div class="cup-auth-hero">
            <span class="cup-kicker text-white-50">Seguridad y usuarios</span>
            <h1 class="mt-2 mb-2">Nueva contraseña</h1>
            <p class="mb-0 text-white-50">Define una contraseña segura para tu cuenta.</p>
        </div>

        <div class="card-body p-4">
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.restablecer.procesar') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nueva contraseña</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password>Mostrar</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password>Mostrar</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Restablecer contraseña</button>
            </form>
        </div>
    </div>
</div>
@endsection