<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CupController;
use App\Http\Controllers\AuthController;

// -------------------------------------------------------------
// RUTA PRINCIPAL
// -------------------------------------------------------------
Route::get('/', function () {
    return redirect('/login');
});

// -------------------------------------------------------------
// RUTAS PÚBLICAS
// No necesitan iniciar sesión
// -------------------------------------------------------------
Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.procesar');
Route::get('/password/recuperar', [AuthController::class, 'mostrarRecuperarPassword'])->name('password.recuperar');
Route::post('/password/recuperar', [AuthController::class, 'procesarRecuperarPassword'])->name('password.recuperar.procesar');
Route::get('/password/restablecer/{token}', [AuthController::class, 'mostrarRestablecerPassword'])->name('password.restablecer');
Route::post('/password/restablecer', [AuthController::class, 'procesarRestablecerPassword'])->name('password.restablecer.procesar');

// Formulario público de inscripción
Route::view('/inscripcion', 'inscripcion');
Route::post('/registrar-postulante', [CupController::class, 'registrarPostulante'])->name('postulante.store');

// Solicitud de cuenta docente
Route::get('/solicitud-docente', [AuthController::class, 'mostrarSolicitudDocente'])->name('solicitud-docente');
Route::post('/solicitud-docente', [AuthController::class, 'procesarSolicitudDocente'])->name('solicitud-docente.procesar');

// -------------------------------------------------------------
// RUTAS PROTEGIDAS
// Solo usuarios autenticados
// -------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // Cerrar sesión: cualquier usuario logueado
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/bitacora', [CupController::class, 'mostrarBitacora'])->name('bitacora.index');
    });

    // ---------------------------------------------------------
    // ADMIN Y COORDINADOR
    // Dashboard, postulantes, docentes y asignaciones
    // ---------------------------------------------------------
    Route::middleware(['role:admin,coordinador'])->group(function () {
        Route::get('/dashboard', [CupController::class, 'mostrarDashboard']);
        Route::get('/postulantes', [CupController::class, 'listarPostulantes'])->name('postulantes.index');
        Route::get('/postulantes/{id}/editar', [CupController::class, 'editarPostulante'])->name('postulantes.editar');
        Route::put('/postulantes/{id}', [CupController::class, 'actualizarPostulante'])->name('postulantes.actualizar');
        Route::delete('/postulantes/{id}', [CupController::class, 'desactivarPostulante'])->name('postulantes.desactivar');
        Route::get('/usuarios/importar', [CupController::class, 'mostrarImportarUsuarios'])->name('usuarios.importar');
        Route::post('/usuarios/importar', [CupController::class, 'importarUsuarios'])->name('usuarios.importar.procesar');
        Route::get('/reportes', [CupController::class, 'mostrarReportes'])->name('reportes.index');
        Route::get('/reportes/exportar/postulantes', [CupController::class, 'exportarPostulantes'])->name('reportes.exportar.postulantes');
        Route::get('/reportes/exportar/carreras', [CupController::class, 'exportarCarreras'])->name('reportes.exportar.carreras');
        Route::get('/reportes/exportar/grupos', [CupController::class, 'exportarGrupos'])->name('reportes.exportar.grupos');
        Route::get('/reportes/exportar/docentes', [CupController::class, 'exportarDocentes'])->name('reportes.exportar.docentes');
        Route::get('/reportes/imprimir', [CupController::class, 'imprimirReportes'])->name('reportes.imprimir');

        Route::get('/revision-requisitos', [CupController::class, 'mostrarRevisionRequisitos'])
            ->name('requisitos.revision');

        Route::post('/revision-requisitos/{id}/aprobar', [CupController::class, 'aprobarRequisitos'])
            ->name('requisitos.aprobar');

        Route::post('/revision-requisitos/{id}/rechazar', [CupController::class, 'rechazarRequisitos'])
            ->name('requisitos.rechazar');

        Route::post('/procesar-admision-carreras', [CupController::class, 'procesarAdmisionCarreras'])
            ->name('admision.procesar');

        // Gestión de docentes
        Route::get('/docentes', [CupController::class, 'mostrarDocentes']);
        Route::post('/docentes', [CupController::class, 'registrarDocente'])->name('docentes.store');
        Route::post('/docentes/{docente}/documentos', [CupController::class, 'guardarDocumentosDocente'])->name('docentes.documentos.guardar');
        Route::post('/docentes/{docente}/documentos/aprobar', [CupController::class, 'aprobarDocumentosDocente'])->name('docentes.documentos.aprobar');
        Route::post('/docentes/{docente}/documentos/rechazar', [CupController::class, 'rechazarDocumentosDocente'])->name('docentes.documentos.rechazar');
        Route::post('/docentes/{docente}/competencias', [CupController::class, 'guardarCompetenciaDocente'])->name('docentes.competencias.guardar');
        Route::post('/docentes/competencias/{competencia}/aprobar', [CupController::class, 'aprobarCompetenciaDocente'])->name('docentes.competencias.aprobar');
        Route::post('/docentes/competencias/{competencia}/rechazar', [CupController::class, 'rechazarCompetenciaDocente'])->name('docentes.competencias.rechazar');

        // Asignación de docentes a grupos
        Route::get('/asignaciones-docentes', [CupController::class, 'mostrarAsignacionesDocentes']);
        Route::post('/asignaciones-docentes', [CupController::class, 'registrarAsignacionDocente'])->name('asignaciones.store');

        // Gestión de aulas
        Route::get('/aulas', [CupController::class, 'mostrarAulas']);
        Route::post('/aulas', [CupController::class, 'registrarAula'])->name('aulas.store');

        // Gestión de horarios de clases
        Route::get('/horarios-clases', [CupController::class, 'mostrarHorariosClases']);
        Route::post('/horarios-clases', [CupController::class, 'registrarHorarioClase'])->name('horarios.store');
        Route::post('/horarios-clases/intensivo', [CupController::class, 'registrarHorarioIntensivo'])->name('horarios.intensivo');
        Route::get('/grupos', [CupController::class, 'mostrarGrupos'])->name('grupos.index');
        Route::post('/grupos/recalcular', [CupController::class, 'recalcularGrupos'])->name('grupos.recalcular');
        Route::post('/grupos/normalizar-nomenclatura', [CupController::class, 'normalizarNomenclaturaGrupos'])->name('grupos.normalizar');
    });

    // ---------------------------------------------------------
    // DOCENTE Y ADMIN
    // Registro de notas
    // ---------------------------------------------------------
    Route::middleware(['role:docente,admin,coordinador'])->group(function () {
        Route::get('/docente-panel', [CupController::class, 'mostrarPanelDocente'])->name('docente.panel');
        Route::get('/notas', [CupController::class, 'mostrarNotas']);
        Route::post('/guardar-notas', [CupController::class, 'guardarNotas']);
        Route::post('/docente-asistencia', [CupController::class, 'registrarAsistenciaDocente'])->name('docente.asistencia.store');
        Route::post('/docente-panel/documentos', [CupController::class, 'guardarMisDocumentosDocente'])->name('docente.documentos.store');
    });

    // ---------------------------------------------------------
    // POSTULANTE
    // Consulta de resultado
    // ---------------------------------------------------------
    Route::middleware(['role:postulante'])->group(function () {
        Route::get('/mi-resultado', [CupController::class, 'mostrarMiResultado']);

        Route::get('/mis-requisitos', [CupController::class, 'mostrarMisRequisitos'])
            ->name('requisitos.mostrar');

        Route::post('/mis-requisitos', [CupController::class, 'guardarMisRequisitos'])
            ->name('requisitos.guardar');
    });
});
