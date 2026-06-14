@extends('layouts.app')

@section('title', 'Inscripción CUP - FICCT')
@section('body_class', 'cup-auth')

@section('auth')
<div class="cup-auth-wide">
    <div class="card border-0 overflow-hidden">
        <div class="cup-auth-hero">
            <h1 class="mt-2 mb-2">Formulario de inscripción</h1>
            <p class="mb-0 text-white-50">Registra tus datos personales, opciones de carrera y credenciales de acceso.</p>
        </div>

        <div class="card-body p-4 p-md-5">
            <div id="alerta-sistema" class="alert d-none" role="alert"></div>

            <form id="form-real-inscripcion">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Cédula de Identidad (CI)</label><input type="text" name="ci" class="form-control" placeholder="Ej: 8765432" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Nombres</label><input type="text" name="nombres" class="form-control" placeholder="Ej: Juan" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Apellidos</label><input type="text" name="apellidos" class="form-control" placeholder="Ej: Pérez Gómez" required></div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Fecha de nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control" required></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Sexo</label><select name="sexo" class="form-select" required><option value="">Seleccione...</option><option value="MASCULINO">Masculino</option><option value="FEMENINO">Femenino</option><option value="OTRO">Otro</option></select></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Teléfono</label><input type="text" name="telefono" class="form-control" placeholder="Ej: 70000000" required></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Ciudad</label><input type="text" name="ciudad" class="form-control" placeholder="Ej: Santa Cruz" required></div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Dirección</label><input type="text" name="direccion" class="form-control" placeholder="Zona, calle, número" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Colegio de procedencia</label><input type="text" name="colegio_procedencia" class="form-control" placeholder="Ej: Colegio Nacional"></div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Primera opción de carrera</label>
                        <select name="carrera_1" class="form-select" required>
                            <option value="">Selecciona una carrera...</option>
                            <option value="Ingeniería Informática">Ingeniería Informática</option>
                            <option value="Ingeniería de Sistemas">Ingeniería de Sistemas</option>
                            <option value="Ingeniería en Redes y Telecomunicaciones">Ingeniería en Redes y Telecomunicaciones</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Segunda opción de carrera</label>
                        <select name="carrera_2" class="form-select">
                            <option value="">Selecciona una carrera...</option>
                            <option value="Ingeniería Informática">Ingeniería Informática</option>
                            <option value="Ingeniería de Sistemas">Ingeniería de Sistemas</option>
                            <option value="Ingeniería en Redes y Telecomunicaciones">Ingeniería en Redes y Telecomunicaciones</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3"><label class="form-label">Otros / observación opcional</label><textarea name="observacion_postulante" class="form-control" rows="2" placeholder="Información adicional relevante"></textarea></div>

                <div class="cup-form-section">
                    <h2 class="cup-section-title mb-3">Datos para iniciar sesión</h2>
                    <div class="mb-3"><label class="form-label">Correo electrónico</label><input type="email" name="correo" class="form-control" placeholder="Ej: postulante@email.com" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Contraseña</label><div class="input-group"><input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required><button class="btn btn-outline-secondary" type="button" data-toggle-password>Mostrar</button></div></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Confirmar contraseña</label><div class="input-group"><input type="password" name="password_confirmation" class="form-control" placeholder="Repite la contraseña" required><button class="btn btn-outline-secondary" type="button" data-toggle-password>Mostrar</button></div></div>
                    </div>
                </div>

                <div class="cup-form-section">
                    <button type="submit" class="btn btn-primary btn-lg w-100">Crear cuenta de postulante</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('form-real-inscripcion').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const alerta = document.getElementById('alerta-sistema');
        alerta.classList.remove('d-none', 'alert-danger', 'alert-success');
        alerta.classList.add('alert-info');
        alerta.innerHTML = 'Procesando registro...';

        try {
            const response = await fetch('/registrar-postulante', { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
            const texto = await response.text();
            let data;
            try { data = JSON.parse(texto); } catch (errorJson) {
                alerta.classList.remove('alert-info'); alerta.classList.add('alert-danger');
                alerta.innerHTML = '<strong>Error del servidor:</strong> Laravel no devolvió JSON.';
                return;
            }
            alerta.classList.remove('alert-info', 'alert-danger', 'alert-success', 'd-none');
            if (!response.ok) {
                alerta.classList.add('alert-danger');
                if (data.errors) {
                    let errores = '<strong>Revisa los datos ingresados:</strong><ul class="mb-0">';
                    Object.values(data.errors).forEach(errorArray => errorArray.forEach(error => errores += `<li>${error}</li>`));
                    alerta.innerHTML = errores + '</ul>';
                } else {
                    alerta.innerHTML = `<strong>Error:</strong> ${data.error || 'No se pudo registrar el postulante.'}`;
                }
                return;
            }
            alerta.classList.add('alert-success');
            alerta.innerHTML = `<strong>¡Registro exitoso!</strong> ${data.message}`;
            form.reset();
        } catch (error) {
            alerta.classList.remove('alert-info'); alerta.classList.add('alert-danger');
            alerta.innerHTML = '<strong>Error:</strong> Problema de comunicación con el servidor.';
        }
    });
</script>
@endpush