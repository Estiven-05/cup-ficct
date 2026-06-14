# Estructura por módulos - CUP FICCT

El sistema mantiene una organización progresiva para no romper rutas ni vistas ya implementadas. Por seguridad, no se movieron de golpe los controladores ni las vistas existentes; se documenta la pertenencia de cada funcionalidad a paquetes conceptuales y los nuevos módulos se ubican en carpetas organizadas.

## Seguridad y usuarios

- Login, logout, roles y bloqueo por intentos: `AuthController`.
- Importación CSV de usuarios: `CupController@mostrarImportarUsuarios` y `CupController@importarUsuarios`.
- Bitácora: `Bitacora`, `BitacoraService`, `resources/views/modules/seguridad_usuarios/bitacora.blade.php`.

## Inscripción y postulantes

- Registro de postulantes: `CupController@registrarPostulante`.
- Requisitos, pago y documentos reales: `mis_requisitos.blade.php`, `revision_requisitos.blade.php`.
- Modelo principal: `Postulante`.

## Gestión académica CUP

- Grupos, notas, horarios manuales e intensivos: métodos de `CupController`.
- Modelos principales: `Grupo`, `Nota`, `HorarioClase`, `AsignacionDocente`.

## Docentes y carga horaria

- Registro de docentes: `docentes.blade.php`.
- Documentos docentes y competencias por materia: `Docente`, `DocenteCompetencia`.
- Panel docente y asistencia: `docente_panel.blade.php`, `AsistenciaDocente`.

## Infraestructura académica

- Aulas, modalidad presencial/virtual y examen presencial: `Aula`, `HorarioClase`.

## Admisión y reportes

- Cupos por carrera y procesamiento de admisión: `Carrera`, `CupController@procesarAdmisionCarreras`.
- Reportes dinámicos, CSV y vista imprimible: `reportes.blade.php`, `reportes_imprimir.blade.php`.

## Decisión técnica

Se mantiene `CupController` como controlador central para evitar un refactor masivo durante el cierre del proyecto. La defensa puede explicar que los métodos están agrupados conceptualmente por paquete, y que la ruta de evolución natural es extraerlos gradualmente a controladores por módulo.

## Bloque final contra enunciado

- Pasarela simulada profesional en `/mis-requisitos`, con métodos digitales, código de transacción generado, fecha/hora, estado `EN_REVISION` y comprobante visual.
- Recuperación académica de contraseña en `/password/recuperar` y `/password/restablecer/{token}`, usando `password_reset_tokens` y enlace visible para pruebas.
- Registro de postulantes con datos completos: fecha de nacimiento, sexo, dirección, teléfono, ciudad y observación opcional.
- Gestión de postulantes con búsqueda, edición y desactivación lógica.
- Módulo formal de grupos en `/grupos`, con fórmula `CEIL(total inscritos / 70)`, recalculo y normalización M001/T001/N001.
- Reportes/resúmenes cerca de módulos: grupos, docentes, notas y dashboard.
- Dashboard administrativo reforzado por paquetes conceptuales del sistema.