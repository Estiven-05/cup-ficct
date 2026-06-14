@extends('layouts.app')

@section('title', 'Importar usuarios - CUP FICCT')
@section('page_kicker', 'Seguridad y usuarios')
@section('page_title', 'Importación de usuarios')
@section('page_description', 'Carga cuentas desde un archivo CSV entregado por gestión académica.')

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa el archivo:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('resultado_importacion'))
        @php($resultado = session('resultado_importacion'))
        <div class="alert alert-success">
            <strong>Importación procesada.</strong><br>
            Creados: {{ $resultado['creados'] }} |
            Omitidos: {{ $resultado['omitidos'] }} |
            Errores: {{ count($resultado['errores']) }}
        </div>

        @if(!empty($resultado['errores']))
            <div class="alert alert-warning">
                <strong>Detalle de errores:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($resultado['errores'] as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="cup-section-title">Cargar CSV de usuarios</h2>
            <p class="cup-muted mb-0">Formato esperado: name,email,password,role</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.importar.procesar') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Archivo CSV</label>
                    <input type="file" name="archivo_csv" class="form-control" accept=".csv,text/csv,text/plain" required>
                    <small class="text-muted">Roles permitidos: admin, coordinador, docente, postulante.</small>
                </div>

                <button type="submit" class="btn btn-primary">Importar usuarios</button>
            </form>
        </div>
    </div>
@endsection
