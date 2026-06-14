# Trazabilidad por paquetes - CUP FICCT

| Paquete | Caso de uso | Ruta | Vista | Controlador/Método | Modelo/Tabla | Estado |
|---|---|---|---|---|---|---|
| Seguridad y usuarios | Iniciar sesión | `/login` | `login.blade.php` | `AuthController@login` | `users`, `bitacoras` | Implementado |
| Seguridad y usuarios | Cerrar sesión | `/logout` | Layout | `AuthController@logout` | `users`, `bitacoras` | Implementado |
| Seguridad y usuarios | Bloqueo por intentos fallidos | `/login` | `login.blade.php` | `AuthController@login` | Cache RateLimiter, `bitacoras` | Implementado |
| Seguridad y usuarios | Importar usuarios por CSV | `/usuarios/importar` | `importar_usuarios.blade.php` | `CupController@importarUsuarios` | `users`, `bitacoras` | Implementado |
| Seguridad y usuarios | Ver bitácora | `/bitacora` | `modules/seguridad_usuarios/bitacora.blade.php` | `CupController@mostrarBitacora` | `bitacoras` | Implementado |
| Inscripción y postulantes | Registrar postulante | `/inscripcion`, `/registrar-postulante` | `inscripcion.blade.php` | `CupController@registrarPostulante` | `postulantes`, `users`, `grupos` | Implementado |
| Inscripción y postulantes | Enviar requisitos, pago y archivos | `/mis-requisitos` | `mis_requisitos.blade.php` | `CupController@guardarMisRequisitos` | `postulantes` | Implementado |
| Inscripción y postulantes | Revisar requisitos | `/revision-requisitos` | `revision_requisitos.blade.php` | `CupController@mostrarRevisionRequisitos` | `postulantes` | Implementado |
| Inscripción y postulantes | Aprobar/rechazar requisitos | `/revision-requisitos/{id}/aprobar`, `/rechazar` | `revision_requisitos.blade.php` | `CupController@aprobarRequisitos`, `rechazarRequisitos` | `postulantes`, `bitacoras` | Implementado |
| Gestión académica CUP | Registrar notas | `/notas`, `/guardar-notas` | `notas.blade.php` | `CupController@mostrarNotas`, `guardarNotas` | `notas`, `postulantes` | Implementado |
| Gestión académica CUP | Generar horario intensivo | `/horarios-clases/intensivo` | `horarios_clases.blade.php` | `CupController@registrarHorarioIntensivo` | `horarios_clases`, `grupos` | Implementado |
| Gestión académica CUP | Normalizar grupos por turno | `/grupos/normalizar-nomenclatura` | `horarios_clases.blade.php` | `CupController@normalizarNomenclaturaGrupos` | `grupos` | Implementado |
| Docentes y carga horaria | Registrar docente | `/docentes` | `docentes.blade.php` | `CupController@registrarDocente` | `docentes`, `bitacoras` | Implementado |
| Docentes y carga horaria | Subir documentos docentes | `/docentes/{docente}/documentos` | `docentes.blade.php` | `CupController@guardarDocumentosDocente` | `docentes` | Implementado |
| Docentes y carga horaria | Registrar competencias docentes | `/docentes/{docente}/competencias` | `docentes.blade.php` | `CupController@guardarCompetenciaDocente` | `docente_competencias` | Implementado |
| Docentes y carga horaria | Aprobar/rechazar competencias | `/docentes/competencias/{competencia}/aprobar`, `/rechazar` | `docentes.blade.php` | `CupController@aprobarCompetenciaDocente`, `rechazarCompetenciaDocente` | `docente_competencias` | Implementado |
| Docentes y carga horaria | Panel docente | `/docente-panel` | `docente_panel.blade.php` | `CupController@mostrarPanelDocente` | `docentes`, `asignaciones_docentes`, `horarios_clases` | Implementado |
| Docentes y carga horaria | Asistencia docente | `/docente-asistencia` | `docente_panel.blade.php` | `CupController@registrarAsistenciaDocente` | `asistencias_docentes` | Implementado |
| Infraestructura académica | Gestionar aulas | `/aulas` | `aulas.blade.php` | `CupController@mostrarAulas`, `registrarAula` | `aulas` | Implementado |
| Infraestructura académica | Programar modalidad y examen presencial | `/horarios-clases` | `horarios_clases.blade.php` | `CupController@registrarHorarioClase` | `horarios_clases`, `aulas` | Implementado |
| Admisión y reportes | Procesar admisión por cupos | `/procesar-admision-carreras` | `dashboard.blade.php` | `CupController@procesarAdmisionCarreras` | `carreras`, `postulantes`, `notas` | Implementado |
| Admisión y reportes | Consultar reportes dinámicos | `/reportes` | `reportes.blade.php` | `CupController@mostrarReportes` | `postulantes`, `carreras`, `grupos`, `docentes` | Implementado |
| Admisión y reportes | Exportar CSV | `/reportes/exportar/*` | N/A | `CupController@exportarPostulantes`, `exportarCarreras`, `exportarGrupos`, `exportarDocentes` | Datos filtrados de reportes, `bitacoras` | Implementado |
| Admisión y reportes | Vista imprimible para PDF | `/reportes/imprimir` | `reportes_imprimir.blade.php` | `CupController@imprimirReportes` | Datos filtrados de reportes, `bitacoras` | Implementado |

| Paquete | Caso de uso | Ruta | Vista | Controlador/Método | Modelo/Tabla | Estado |
|---|---|---|---|---|---|---|
| Seguridad y usuarios | Solicitar recuperación de contraseña | GET/POST `/password/recuperar` | `password_recuperar.blade.php` | `AuthController@mostrarRecuperarPassword`, `procesarRecuperarPassword` | `users`, `password_reset_tokens` | Implementado |
| Seguridad y usuarios | Restablecer contraseña | GET `/password/restablecer/{token}`, POST `/password/restablecer` | `password_restablecer.blade.php` | `AuthController@mostrarRestablecerPassword`, `procesarRestablecerPassword` | `users`, `password_reset_tokens` | Implementado |
| Inscripción y postulantes | Registro con datos completos | GET `/inscripcion`, POST `/registrar-postulante` | `inscripcion.blade.php` | `CupController@registrarPostulante` | `postulantes`, `users`, `grupos` | Implementado |
| Inscripción y postulantes | Buscar/listar postulantes | GET `/postulantes` | `postulantes_index.blade.php` | `CupController@listarPostulantes` | `postulantes`, `notas`, `grupos`, `users` | Implementado |
| Inscripción y postulantes | Editar postulante | GET `/postulantes/{id}/editar`, PUT `/postulantes/{id}` | `postulantes_editar.blade.php` | `CupController@editarPostulante`, `actualizarPostulante` | `postulantes`, `users` | Implementado |
| Inscripción y postulantes | Desactivar postulante | DELETE `/postulantes/{id}` | `postulantes_index.blade.php` | `CupController@desactivarPostulante` | `postulantes.estado_registro` | Implementado |
| Inscripción y postulantes | Pasarela simulada CUP | GET/POST `/mis-requisitos` | `mis_requisitos.blade.php` | `CupController@mostrarMisRequisitos`, `guardarMisRequisitos` | `postulantes` | Implementado |
| Gestión académica CUP | Módulo formal de grupos | GET `/grupos` | `grupos.blade.php` | `CupController@mostrarGrupos` | `grupos`, `postulantes` | Implementado |
| Gestión académica CUP | Recalcular grupos CEIL(total / 70) | POST `/grupos/recalcular` | `grupos.blade.php` | `CupController@recalcularGrupos` | `grupos`, `postulantes` | Implementado |
| Gestión académica CUP | Normalizar nomenclatura | POST `/grupos/normalizar-nomenclatura` | `grupos.blade.php` | `CupController@normalizarNomenclaturaGrupos` | `grupos` | Implementado |
| Admisión y reportes | Reportes por módulo en dashboard | GET `/dashboard` | `dashboard.blade.php` | `CupController@mostrarDashboard` | `postulantes`, `notas`, `docentes`, `bitacoras` | Implementado |
| Docentes y carga horaria | Resumen docente por módulo | GET `/docentes` | `docentes.blade.php` | `CupController@mostrarDocentes` | `docentes`, `docente_competencias` | Implementado |
| Gestión académica CUP | Estadísticas por materia | GET `/notas` | `notas.blade.php` | `CupController@mostrarNotas` | `notas` | Implementado |