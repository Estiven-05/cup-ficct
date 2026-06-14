<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CUP FICCT')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/cup-theme.css') }}" rel="stylesheet">
</head>
<body class="@yield('body_class')">

@hasSection('auth')
    @yield('auth')
@else
    @php
        $role = auth()->user()->role ?? null;
        $homeUrl = $role === 'postulante'
            ? url('/mi-resultado')
            : ($role === 'docente' ? url('/docente-panel') : url('/dashboard'));
    @endphp

    <div class="cup-shell">
        <aside class="cup-sidebar">
            <a class="cup-brand" href="{{ $homeUrl }}">
                <span class="cup-brand-mark">C</span>
                <span>
                    <strong>CUP FICCT</strong>
                    <small>Admisión universitaria</small>
                </span>
            </a>

            <div class="cup-nav-label">Módulos del sistema</div>

            <nav class="cup-nav" aria-label="Navegación principal">
                @if(in_array($role, ['admin', 'coordinador']))
                    <a class="{{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">Dashboard</a>
                    <a class="{{ request()->is('postulantes') ? 'active' : '' }}" href="{{ url('/postulantes') }}">Postulantes</a>
                    <a class="{{ request()->is('usuarios/importar') ? 'active' : '' }}" href="{{ route('usuarios.importar') }}">Importar usuarios</a>
                    @if($role === 'admin')
                        <a class="{{ request()->is('bitacora') ? 'active' : '' }}" href="{{ route('bitacora.index') }}">Bitácora</a>
                    @endif
                    <a class="{{ request()->is('revision-requisitos') ? 'active' : '' }}" href="{{ url('/revision-requisitos') }}">Revisión de requisitos</a>
                    <a class="{{ request()->is('docentes') ? 'active' : '' }}" href="{{ url('/docentes') }}">Docentes</a>
                    <a class="{{ request()->is('asignaciones-docentes') ? 'active' : '' }}" href="{{ url('/asignaciones-docentes') }}">Asignaciones</a>
                    <a class="{{ request()->is('grupos') ? 'active' : '' }}" href="{{ route('grupos.index') }}">Grupos</a>
                    <a class="{{ request()->is('docente-panel') ? 'active' : '' }}" href="{{ url('/docente-panel') }}">Panel docente</a>
                    <a class="{{ request()->is('aulas') ? 'active' : '' }}" href="{{ url('/aulas') }}">Aulas</a>
                    <a class="{{ request()->is('horarios-clases') ? 'active' : '' }}" href="{{ url('/horarios-clases') }}">Horarios</a>
                    <a class="{{ request()->is('notas') ? 'active' : '' }}" href="{{ url('/notas') }}">Notas</a>
                    <a class="{{ request()->is('reportes') ? 'active' : '' }}" href="{{ route('reportes.index') }}">Reportes</a>
                @elseif($role === 'docente')
                    <a class="{{ request()->is('docente-panel') ? 'active' : '' }}" href="{{ url('/docente-panel') }}">Panel docente</a>
                    <a class="{{ request()->is('notas') ? 'active' : '' }}" href="{{ url('/notas') }}">Notas</a>
                @elseif($role === 'postulante')
                    <a class="{{ request()->is('mi-resultado') ? 'active' : '' }}" href="{{ url('/mi-resultado') }}">Mi resultado</a>
                    <a class="{{ request()->is('mis-requisitos') ? 'active' : '' }}" href="{{ url('/mis-requisitos') }}">Mis requisitos y pago</a>
                @endif
            </nav>

            <div class="cup-sidebar-footer">
                @auth
                    @php
                        $roleText = match ($role) {
                            'admin' => 'ADMINISTRADOR',
                            'coordinador' => 'COORDINADOR',
                            'docente' => 'DOCENTE',
                            'postulante' => 'POSTULANTE',
                            default => strtoupper($role),
                        };
                    @endphp
                    <div class="text-center mb-3">
                        <span class="badge cup-badge-aprobado text-uppercase fw-bold px-3 py-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                            {{ $roleText }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">Cerrar sesión</button>
                    </form>
                @endauth
            </div>
        </aside>

        <main class="cup-main">
            <header class="cup-topbar">
                <div>
                    <span class="cup-kicker">@yield('page_kicker', 'Sistema CUP FICCT')</span>
                    <h1>@yield('page_title', 'Panel de gestión')</h1>
                    @hasSection('page_description')
                        <p>@yield('page_description')</p>
                    @endif
                </div>

                <div class="cup-topbar-actions">
                    @yield('page_actions')
                </div>
            </header>

            <div class="cup-content">
                @yield('content')
            </div>
        </main>
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-toggle-password]');

        if (!button) {
            return;
        }

        const group = button.closest('.input-group');
        const input = group ? group.querySelector('input[type="password"], input[type="text"]') : null;

        if (!input) {
            return;
        }

        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.textContent = showing ? 'Mostrar' : 'Ocultar';
    });
</script>
@stack('scripts')
</body>
</html>
