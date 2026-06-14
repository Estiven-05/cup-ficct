<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Postulante;
use App\Models\Grupo;
use App\Models\Nota;
use App\Models\Docente;
use App\Models\AsignacionDocente;
use App\Models\Aula;
use App\Models\HorarioClase;
use App\Models\AsistenciaDocente;
use App\Models\Bitacora;
use App\Models\DocenteCompetencia;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Carrera;

class CupController extends Controller
{
    // Muestra la vista de inscripción
    public function mostrarFormulario()
    {
        return view('inscripcion');
    }

    // Registra postulantes, crea usuario de login y asigna grupo automático
    public function registrarPostulante(Request $request)
    {
    // 1. Validación básica del formulario
    $validator = Validator::make($request->all(), [
    'ci' => 'required|unique:postulantes,ci',
    'nombres' => 'required',
    'apellidos' => 'required',
    'carrera_1' => 'required',
    'carrera_2' => 'nullable',
    'colegio_procedencia' => 'nullable',

    'correo' => 'required|email|unique:users,email',
    'password' => 'required|string|min:6|confirmed',
    ], [
    'ci.required' => 'La cédula de identidad es obligatoria.',
    'ci.unique' => 'La cédula de identidad ya está registrada.',

    'nombres.required' => 'El nombre del postulante es obligatorio.',
    'apellidos.required' => 'El apellido del postulante es obligatorio.',

    'carrera_1.required' => 'Debe seleccionar la primera opción de carrera.',

    'correo.required' => 'El correo electrónico es obligatorio.',
    'correo.email' => 'Debe ingresar un correo electrónico válido.',
    'correo.unique' => 'Este correo electrónico ya está registrado.',

    'password.required' => 'La contraseña es obligatoria.',
    'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
    'password.confirmed' => 'La confirmación de contraseña no coincide.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    // 2. Validar requisitos obligatorios del CUP
    if (!$request->has('titulo_bachiller') || !$request->has('estado_pago')) {
        return response()->json([
            'error' => 'No cumple con los requisitos obligatorios del CUP.'
        ], 400);
    }

    return DB::transaction(function () use ($request) {

        // 3. Crear usuario de login para el postulante
        $usuario = User::create([
            'name' => $request->nombres . ' ' . $request->apellidos,
            'email' => $request->correo,
            'password' => Hash::make($request->password),
            'role' => 'postulante',
        ]);

        // 4. Buscar grupo con cupo disponible
        $grupoAsignado = Grupo::where('total_inscritos', '<', 70)
            ->orderBy('id', 'asc')
            ->first();

        // 5. Si no hay grupo disponible, crear uno nuevo
        if (!$grupoAsignado) {
            $turnoGrupo = 'MAÃƒÆ’Ã¢â‚¬�‹Å“ANA';

            $grupoAsignado = Grupo::create([
                'nombre_grupo' => $this->siguienteNombreGrupo($turnoGrupo),
                'turno' => $turnoGrupo,
                'cupo_maximo' => 70,
                'total_inscritos' => 0,
            ]);
        }

        // 6. Registrar postulante vinculado al usuario
        $postulante = Postulante::create([
            'user_id' => $usuario->id,
            'ci' => $request->ci,
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'carrera_1' => $request->carrera_1,
            'carrera_2' => $request->carrera_2,
            'colegio_procedencia' => $request->colegio_procedencia,
            'titulo_bachiller' => true,
            'estado_pago' => true,
            'estado_inscripcion' => 'pendiente',
            'grupo_id' => $grupoAsignado->id,
        ]);

        // 7. Actualizar cantidad de inscritos del grupo
        $grupoAsignado->increment('total_inscritos');

        BitacoraService::registrar(
            'Registro de postulante',
            'Inscripción y postulantes',
            'Se registró el postulante #' . $postulante->id . ' y se creó su usuario de acceso.',
            $request,
            $usuario
        );

        return response()->json([
            'message' => 'Postulante inscrito con éxito. Ya puede iniciar sesión con su correo y contraseña.',
            'usuario_login' => [
                'email' => $usuario->email,
                'role' => $usuario->role,
            ],
            'postulante' => $postulante,
            'grupo' => $grupoAsignado,
        ]);
    });
    }
    public function mostrarNotas()
    {
        $usuario = auth()->user();
        $materiasBase = ['Computación', 'Matemáticas', 'Inglés', 'Física'];
        $docenteActual = $this->docenteActual();

        if ($usuario->role === 'docente') {
            if (!$docenteActual) {
                return view('notas', [
                    'postulantes' => collect(),
                    'materiasDisponibles' => collect(),
                    'docenteActual' => null,
                ]);
            }

            $asignaciones = AsignacionDocente::where('docente_id', $docenteActual->id)
                ->where('estado', 'ACTIVA')
                ->with('grupo')
                ->get();

            $grupoIds = $asignaciones->pluck('grupo_id')->unique()->values();
            $materiasDisponibles = $asignaciones->pluck('materia')->unique()->values();
            $postulantes = Postulante::with(['grupo', 'notas'])
                ->whereIn('grupo_id', $grupoIds)
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get();

            return view('notas', compact('postulantes', 'materiasDisponibles', 'docenteActual'));
        }

        $postulantes = Postulante::with(['grupo', 'notas'])
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $materiasDisponibles = collect($materiasBase);

        return view('notas', compact('postulantes', 'materiasDisponibles', 'docenteActual'));
    }

    public function guardarNotas(Request $request)
    {
        $request->validate([
            'postulante_id' => 'required|exists:postulantes,id',
            'materia' => 'required|string',
            'eval1' => 'required|numeric|min:0|max:30',
            'eval2' => 'required|numeric|min:0|max:30',
            'eval3' => 'required|numeric|min:0|max:40',
        ], [
            'postulante_id.required' => 'Debe seleccionar un postulante.',
            'postulante_id.exists' => 'El postulante seleccionado no existe.',
            'eval1.max' => 'La evaluación 1 no puede superar 30 puntos.',
            'eval2.max' => 'La evaluación 2 no puede superar 30 puntos.',
            'eval3.max' => 'La evaluación final no puede superar 40 puntos.',
        ]);

        $postulante = Postulante::with('grupo')->findOrFail($request->postulante_id);
        $materia = $this->normalizarMateria($request->materia);
        $columnas = $this->columnasMateria($materia);

        if (!$columnas) {
            return redirect()->back()->withErrors([
                'materia' => 'La materia seleccionada no es válida.',
            ])->withInput();
        }

        if (auth()->user()->role === 'docente') {
            $docente = $this->docenteActual();

            $asignacionValida = $docente && AsignacionDocente::where('docente_id', $docente->id)
                ->where('grupo_id', $postulante->grupo_id)
                ->where('materia', $materia)
                ->where('estado', 'ACTIVA')
                ->exists();

            if (!$asignacionValida) {
                return redirect()->back()->withErrors([
                    'materia' => 'No tiene permiso para registrar esta materia o grupo.',
                ])->withInput();
            }
        }

        $nota = Nota::firstOrCreate(['postulante_id' => $postulante->id]);
        $nota->{$columnas[0]} = $request->eval1;
        $nota->{$columnas[1]} = $request->eval2;
        $nota->{$columnas[2]} = $request->eval3;

        $totales = $this->totalesMaterias($nota);
        $materiasPendientes = collect($totales)->filter(fn ($total) => $total <= 0)->count() > 0;
        $promedioFinal = array_sum($totales) / 4;
        $estado = $materiasPendientes
            ? 'PENDIENTE'
            : (collect($totales)->every(fn ($total) => $total >= 60) && $promedioFinal >= 60 ? 'APROBADO' : 'REPROBADO');

        $nota->promedio = $promedioFinal;
        $nota->estado = $estado;
        $nota->save();

        Postulante::where('id', $request->postulante_id)->update([
            'estado_inscripcion' => $estado === 'PENDIENTE' ? 'pendiente' : 'evaluado',
        ]);

        BitacoraService::registrar(
            'Registro o actualización de notas',
            'Gestión académica CUP',
            'Se registraron notas de ' . $materia . ' para el postulante #' . $postulante->id . '.',
            $request
        );

        return redirect()->back()->with(
            'exito',
            'Notas registradas correctamente. Promedio final: ' . number_format($promedioFinal, 2) . ' - Estado: ' . $estado
        );
    }
    // Procesa la admisión final según promedio y cupos por carrera
    public function procesarAdmisionCarreras()
    {
        DB::transaction(function () {

            // 1. Reiniciar cupos ocupados para recalcular desde cero
            Carrera::query()->update([
                'cupos_ocupados' => 0,
            ]);

            // 2. Reiniciar estado de admisión de todos los postulantes
            Postulante::query()->update([
                'carrera_asignada_id' => null,
                'estado_admision' => 'PENDIENTE',
                'tipo_asignacion' => null,
                'observacion_admision' => null,
            ]);

            // 3. Marcar como NO ADMITIDOS a los postulantes reprobados
            $reprobados = Postulante::with('notas')
                ->whereHas('notas', function ($query) {
                    $query->where('estado', 'REPROBADO');
                })
                ->get();

            foreach ($reprobados as $postulante) {
                $postulante->update([
                    'estado_admision' => 'NO_ADMITIDO',
                    'tipo_asignacion' => 'REPROBADO',
                    'observacion_admision' => 'El postulante no alcanzó el mínimo requerido para ser admitido.',
                ]);
            }

            // 4. Obtener aprobados ordenados por mejor promedio
            $aprobados = Postulante::with('notas')
                ->whereHas('notas', function ($query) {
                    $query->where('estado', 'APROBADO');
                })
                ->get()
                ->sortByDesc(function ($postulante) {
                    return $postulante->notas->promedio;
                });

            // 5. Asignar cupos por mérito académico
            foreach ($aprobados as $postulante) {

                // Intentar asignar carrera 1
                $carreraPrimeraOpcion = Carrera::where('nombre', $postulante->carrera_1)
                    ->where('estado', 'ACTIVA')
                    ->first();

                if (
                    $carreraPrimeraOpcion &&
                    $carreraPrimeraOpcion->cupos_ocupados < $carreraPrimeraOpcion->cupo_maximo
                ) {
                    $postulante->update([
                        'carrera_asignada_id' => $carreraPrimeraOpcion->id,
                        'estado_admision' => 'ADMITIDO',
                        'tipo_asignacion' => 'CARRERA_1',
                        'observacion_admision' => 'Admitido en su primera opción de carrera.',
                    ]);

                    $carreraPrimeraOpcion->increment('cupos_ocupados');
                    continue;
                }

                // Intentar asignar carrera 2
                $carreraSegundaOpcion = Carrera::where('nombre', $postulante->carrera_2)
                    ->where('estado', 'ACTIVA')
                    ->first();

                if (
                    $carreraSegundaOpcion &&
                    $carreraSegundaOpcion->cupos_ocupados < $carreraSegundaOpcion->cupo_maximo
                ) {
                    $postulante->update([
                        'carrera_asignada_id' => $carreraSegundaOpcion->id,
                        'estado_admision' => 'ADMITIDO',
                        'tipo_asignacion' => 'CARRERA_2',
                        'observacion_admision' => 'Admitido en su segunda opción porque la primera opción no tenía cupo disponible.',
                    ]);

                    $carreraSegundaOpcion->increment('cupos_ocupados');
                    continue;
                }

                // Si no hay cupo en ninguna carrera
                $postulante->update([
                    'carrera_asignada_id' => null,
                    'estado_admision' => 'NO_ADMITIDO',
                    'tipo_asignacion' => 'SIN_CUPO',
                    'observacion_admision' => 'El postulante aprobó el CUP, pero no encontró cupo disponible en sus opciones de carrera.',
                ]);
            }
        });

        BitacoraService::registrar(
            'Procesamiento de admisión por cupos',
            'Admisión y reportes',
            'Se ejecutó el procesamiento de admisión por cupos de carrera.',
            request()
        );

        return redirect()->back()->with(
            'exito',
            'Proceso de admisión por cupos ejecutado correctamente.'
        );
    }

       // Muestra el Panel Administrativo completo con reportes
    public function mostrarDashboard()
    {
        // Estadísticas de postulantes
        $totalInscritos = Postulante::count();
        $totalAprobados = Nota::where('estado', 'APROBADO')->count();

        $totalReprobadosConNota = Nota::where('estado', 'REPROBADO')->count();
        $totalSinNota = $totalInscritos - Nota::count();
        $totalReprobados = $totalReprobadosConNota + $totalSinNota;

        // Estadísticas de grupos
        $totalGrupos = Grupo::count();

        // Estadísticas de docentes
        $totalDocentes = Docente::count();
        $totalDocentesHabilitados = Docente::where('estado', 'HABILITADO')->count();
        $totalDocentesPendientes = Docente::where('estado', 'PENDIENTE')->count();

        // Estadísticas de asignaciones docentes
        $totalAsignacionesDocentes = AsignacionDocente::where('estado', 'ACTIVA')->count();

        // Estadísticas de aulas
        $totalAulas = Aula::count();
        $totalAulasDisponibles = Aula::where('estado', 'DISPONIBLE')->count();
        $totalAulasNoDisponibles = Aula::where('estado', 'NO DISPONIBLE')->count();

        // Estadísticas de horarios
        $totalHorariosClases = HorarioClase::where('estado', 'ACTIVO')->count();

        // Listado general de postulantes
        $todosLosPostulantes = Postulante::with('grupo')
            ->orderBy('id', 'desc')
            ->get();

        // Lista de aprobados
        $listaAprobados = Postulante::join('notas', 'postulantes.id', '=', 'notas.postulante_id')
            ->where('notas.estado', 'APROBADO')
            ->select('postulantes.*', 'notas.promedio')
            ->orderBy('notas.promedio', 'desc')
            ->get();

        // Lista de reprobados
        $listaReprobados = Postulante::join('notas', 'postulantes.id', '=', 'notas.postulante_id')
            ->where('notas.estado', 'REPROBADO')
            ->select('postulantes.*', 'notas.promedio')
            ->orderBy('notas.promedio', 'desc')
            ->get();

        // Lista de pendientes sin nota
        $idsConNota = Nota::pluck('postulante_id')->toArray();

        $listaPendientes = Postulante::whereNotIn('id', $idsConNota)
            ->orderBy('id', 'desc')
            ->get();

        return view('dashboard', compact(
            'totalInscritos',
            'totalAprobados',
            'totalReprobados',
            'totalGrupos',
            'totalDocentes',
            'totalDocentesHabilitados',
            'totalDocentesPendientes',
            'totalAsignacionesDocentes',
            'totalAulas',
            'totalAulasDisponibles',
            'totalAulasNoDisponibles',
            'totalHorariosClases',
            'todosLosPostulantes',
            'listaAprobados',
            'listaReprobados',
            'listaPendientes'
        ));
    }
        // Muestra el listado completo e independiente de postulantes
    public function listarPostulantes()
    {
        // Jalamos los postulantes con su grupo correspondiente para no sobrecargar la base
        $postulantes = Postulante::with('grupo')->orderBy('id', 'asc')->get();
        
        return view('postulantes_index', compact('postulantes'));
    }

    // Muestra el panel del postulante con sus datos reales
    public function mostrarMiResultado()
    {
        $postulante = Postulante::with([
                'grupo.asignaciones.docente',
                'grupo.asignaciones.horarios.aula',
                'notas',
                'carreraAsignada',
            ])
            ->where('user_id', auth()->id())
            ->first();

        if (!$postulante) {
            return view('mi_resultado', [
                'postulante' => null,
                'nota' => null,
                'horariosCup' => collect(),
            ]);
        }

        $nota = $postulante->notas;
        $horariosCup = $postulante->grupo
            ? $postulante->grupo->asignaciones
                ->flatMap(fn ($asignacion) => $asignacion->horarios)
                ->sortBy(fn ($horario) => $this->ordenDia($horario->dia) . '-' . $horario->hora_inicio)
                ->values()
            : collect();

        return view('mi_resultado', compact('postulante', 'nota', 'horariosCup'));
    }

    // Muestra la pantalla donde el postulante completa documentos y pago
    public function mostrarMisRequisitos()
    {
        $postulante = Postulante::where('user_id', auth()->id())->first();

        if (!$postulante) {
            return redirect('/mi-resultado')->withErrors([
                'error' => 'No se encontró un registro de postulante vinculado a este usuario.',
            ]);
        }

        return view('mis_requisitos', compact('postulante'));
    }


    // Guarda los documentos y el pago simulado del postulante
    public function guardarMisRequisitos(Request $request)
    {
        $postulante = Postulante::where('user_id', auth()->id())->first();

        if (!$postulante) {
            return redirect('/mi-resultado')->withErrors([
                'error' => 'No se encontró un registro de postulante vinculado a este usuario.',
            ]);
        }

        $request->validate([
            'metodo_pago' => 'required|in:PayPal,Stripe,Billetera Digital',
            'codigo_transaccion' => 'required|string|min:5|max:100',
            'monto_pago' => 'required|numeric|min:1',
            'archivo_fotocopia_ci' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_titulo_bachiller' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_certificado_nacimiento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_fotografia' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_formulario_inscripcion' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'metodo_pago.required' => 'Debe seleccionar un método de pago.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
            'codigo_transaccion.required' => 'Debe ingresar el código de transacción.',
            'codigo_transaccion.min' => 'El código de transacción debe tener al menos 5 caracteres.',
            'monto_pago.required' => 'Debe ingresar el monto pagado.',
            'monto_pago.numeric' => 'El monto pagado debe ser un número válido.',
            'monto_pago.min' => 'El monto pagado debe ser mayor a 0.',
        ]);

        if (
            !$request->has('doc_fotocopia_ci') ||
            !$request->has('doc_titulo_bachiller') ||
            !$request->has('doc_certificado_nacimiento') ||
            !$request->has('doc_fotografia') ||
            !$request->has('doc_formulario_inscripcion')
        ) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'documentos' => 'Debe marcar todos los documentos requeridos antes de enviar la revisión.',
                ]);
        }

        $archivos = [];

        foreach ([
            'archivo_fotocopia_ci',
            'archivo_titulo_bachiller',
            'archivo_certificado_nacimiento',
            'archivo_fotografia',
            'archivo_formulario_inscripcion',
        ] as $campoArchivo) {
            if ($request->hasFile($campoArchivo)) {
                $archivos[$campoArchivo] = $request->file($campoArchivo)
                    ->store('postulantes/' . $postulante->id, 'public');
            }
        }

        $postulante->update(array_merge([
            'doc_fotocopia_ci' => true,
            'doc_titulo_bachiller' => true,
            'doc_certificado_nacimiento' => true,
            'doc_fotografia' => true,
            'doc_formulario_inscripcion' => true,

            'metodo_pago' => $request->metodo_pago,
            'codigo_transaccion' => $request->codigo_transaccion,
            'monto_pago' => $request->monto_pago,

            'estado_pago_revision' => 'EN_REVISION',
            'estado_requisitos' => 'EN_REVISION',
            'observacion_requisitos' => 'Requisitos enviados por el postulante para revisión del coordinador.',
            'fecha_envio_requisitos' => now(),
            'fecha_validacion_requisitos' => null,
        ], $archivos));

        BitacoraService::registrar(
            'Envío de requisitos y pago',
            'Inscripción y postulantes',
            'El postulante #' . $postulante->id . ' envió requisitos, pago y documentos para revisión.',
            $request
        );

        return redirect()->back()->with(
            'exito',
            'Requisitos y pago enviados correctamente. Ahora quedan pendientes de revisión por el coordinador académico.'
        );
    }   

    // Muestra la pantalla de revisión de requisitos y pago para admin/coordinador
public function mostrarRevisionRequisitos()
{
    $postulantes = Postulante::with(['grupo', 'notas'])
        ->orderBy('id', 'asc')
        ->get();

    return view('revision_requisitos', compact('postulantes'));
}


// Aprueba requisitos y pago del postulante
public function aprobarRequisitos($id)
{
    $postulante = Postulante::findOrFail($id);

    $documentosCompletos =
        $postulante->doc_fotocopia_ci &&
        $postulante->doc_titulo_bachiller &&
        $postulante->doc_certificado_nacimiento &&
        $postulante->doc_fotografia &&
        $postulante->doc_formulario_inscripcion;

    $archivosCompletos =
        filled($postulante->archivo_fotocopia_ci) &&
        filled($postulante->archivo_titulo_bachiller) &&
        filled($postulante->archivo_certificado_nacimiento) &&
        filled($postulante->archivo_fotografia) &&
        filled($postulante->archivo_formulario_inscripcion);

    $pagoCompleto =
        filled($postulante->metodo_pago) &&
        filled($postulante->codigo_transaccion) &&
        !is_null($postulante->monto_pago) &&
        (float) $postulante->monto_pago > 0;

    if (!$documentosCompletos || !$archivosCompletos || !$pagoCompleto) {
        return redirect()->back()->withErrors([
            'requisitos' => 'No se puede aprobar: faltan documentos marcados, archivos requeridos o datos de pago.',
        ]);
    }

    $postulante->update([
        'estado_pago_revision' => 'APROBADO',
        'estado_requisitos' => 'APROBADO',
        'estado_pago' => true,
        'estado_inscripcion' => 'habilitado_cup',
        'observacion_requisitos' => 'Requisitos y pago aprobados por el coordinador académico.',
        'fecha_validacion_requisitos' => now(),
    ]);

    BitacoraService::registrar(
        'Aprobación de requisitos',
        'Inscripción y postulantes',
        'Se aprobaron requisitos y pago del postulante #' . $postulante->id . '.',
        request()
    );

    return redirect()->back()->with(
        'exito',
        'Requisitos y pago aprobados correctamente para ' .
        $postulante->nombres . ' ' . $postulante->apellidos . '.'
    );
}


// Rechaza requisitos y pago del postulante
public function rechazarRequisitos(Request $request, $id)
{
    $request->validate([
        'observacion_requisitos' => 'required|string|min:5|max:500',
    ], [
        'observacion_requisitos.required' => 'Debe ingresar una observación para rechazar.',
        'observacion_requisitos.min' => 'La observación debe tener al menos 5 caracteres.',
    ]);

    $postulante = Postulante::findOrFail($id);

    $postulante->update([
        'estado_pago_revision' => 'RECHAZADO',
        'estado_requisitos' => 'RECHAZADO',
        'estado_pago' => false,
        'estado_inscripcion' => 'observado',
        'observacion_requisitos' => $request->observacion_requisitos,
        'fecha_validacion_requisitos' => now(),
    ]);

    BitacoraService::registrar(
        'Rechazo de requisitos',
        'Inscripción y postulantes',
        'Se rechazaron requisitos del postulante #' . $postulante->id . '.',
        $request
    );

    return redirect()->back()->with(
        'exito',
        'Requisitos rechazados correctamente para ' .
        $postulante->nombres . ' ' . $postulante->apellidos . '.'
    );
}

    public function mostrarReportes(Request $request)
    {
        return view('reportes', $this->obtenerDatosReportes($request));
    }

    public function imprimirReportes(Request $request)
    {
        BitacoraService::registrar(
            'Apertura de vista imprimible de reportes',
            'Admisión y reportes',
            'Se abrió la vista imprimible de reportes con filtros dinámicos.',
            $request
        );

        return view('reportes_imprimir', $this->obtenerDatosReportes($request));
    }

    public function exportarPostulantes(Request $request)
    {
        $datos = $this->obtenerDatosReportes($request);

        $filas = $datos['postulantes']->map(function ($postulante) {
            return [
                $postulante->id,
                $postulante->ci,
                trim($postulante->nombres . ' ' . $postulante->apellidos),
                $postulante->carrera_1,
                $postulante->carrera_2 ?? 'No registrada',
                $postulante->carreraAsignada->nombre ?? 'Sin asignar',
                $postulante->grupo->nombre_grupo ?? 'Sin grupo',
                $postulante->notas ? number_format((float) $postulante->notas->promedio, 2, '.', '') : 'Pendiente',
                $postulante->notas->estado ?? 'SIN NOTAS',
                $postulante->estado_admision ?? 'PENDIENTE',
                $this->textoEstadoRevision($postulante->estado_requisitos),
                $this->textoEstadoRevision($postulante->estado_pago_revision),
                $postulante->metodo_pago ?? 'No registrado',
                $postulante->codigo_transaccion ?? 'No registrado',
                is_null($postulante->monto_pago) ? 'No registrado' : number_format((float) $postulante->monto_pago, 2, '.', ''),
                optional($postulante->created_at)->format('d/m/Y H:i'),
            ];
        });

        BitacoraService::registrar('Exportación de reportes CSV', 'Admisión y reportes', 'Exportación CSV de postulantes filtrados.', $request);

        return $this->descargarCsv('reporte_postulantes.csv', [
            'ID',
            'CI',
            'Nombre completo',
            'Carrera 1',
            'Carrera 2',
            'Carrera asignada',
            'Grupo',
            'Promedio',
            'Estado académico CUP',
            'Estado admisión',
            'Estado requisitos',
            'Estado pago',
            'Método de pago',
            'Código de transacción',
            'Monto pagado',
            'Fecha de registro',
        ], $filas);
    }

    public function exportarCarreras(Request $request)
    {
        $datos = $this->obtenerDatosReportes($request);

        $filas = $datos['reporteCarreras']->map(fn ($fila) => [
            $fila['carrera'],
            $fila['cupo_maximo'],
            $fila['cupos_ocupados'],
            $fila['postulantes_admitidos'],
            $fila['cupos_disponibles'],
        ]);

        BitacoraService::registrar('Exportación de reportes CSV', 'Admisión y reportes', 'Exportación CSV de carreras.', $request);

        return $this->descargarCsv('reporte_carreras.csv', [
            'Carrera',
            'Cupo máximo',
            'Cupos ocupados',
            'Postulantes admitidos',
            'Cupos disponibles',
        ], $filas);
    }

    public function exportarGrupos(Request $request)
    {
        $datos = $this->obtenerDatosReportes($request);

        $filas = $datos['reporteGrupos']->map(fn ($fila) => [
            $fila['grupo'],
            $fila['total_postulantes'],
            $fila['aprobados'],
            $fila['reprobados'],
            $fila['admitidos'],
            is_null($fila['promedio_general']) ? 'Sin notas' : number_format((float) $fila['promedio_general'], 2, '.', ''),
        ]);

        BitacoraService::registrar('Exportación de reportes CSV', 'Admisión y reportes', 'Exportación CSV de grupos.', $request);

        return $this->descargarCsv('reporte_grupos.csv', [
            'Grupo',
            'Total postulantes',
            'Aprobados',
            'Reprobados',
            'Admitidos',
            'Promedio general del grupo',
        ], $filas);
    }

    public function exportarDocentes(Request $request)
    {
        $datos = $this->obtenerDatosReportes($request);

        $filas = $datos['reporteDocentes']->map(fn ($fila) => [
            $fila['docente'],
            $fila['materia'],
            $fila['grupos_asignados'] ?: 'Sin grupos',
            $fila['postulantes_vinculados'],
            $fila['aprobados'],
            number_format((float) $fila['porcentaje_aprobados'], 2, '.', '') . '%',
        ]);

        BitacoraService::registrar('Exportación de reportes CSV', 'Admisión y reportes', 'Exportación CSV de docentes.', $request);

        return $this->descargarCsv('reporte_docentes.csv', [
            'Docente',
            'Materia',
            'Grupos asignados',
            'Cantidad de postulantes vinculados',
            'Cantidad de aprobados',
            'Porcentaje de aprobados',
        ], $filas);
    }

    private function obtenerDatosReportes(Request $request): array
    {
        $carreras = Carrera::orderBy('nombre', 'asc')->get();
        $grupos = Grupo::orderBy('nombre_grupo', 'asc')->get();
        $docentes = Docente::orderBy('apellidos', 'asc')->orderBy('nombres', 'asc')->get();

        $carrerasPostulantes = Postulante::query()
            ->whereNotNull('carrera_1')
            ->select('carrera_1')
            ->distinct()
            ->orderBy('carrera_1', 'asc')
            ->pluck('carrera_1');

        $materias = AsignacionDocente::query()
            ->whereNotNull('materia')
            ->select('materia')
            ->distinct()
            ->orderBy('materia', 'asc')
            ->pluck('materia');

        $turnos = ['MAÃƒÆ’Ã¢â‚¬�‹Å“ANA', 'TARDE', 'NOCHE'];
        $modalidades = ['PRESENCIAL', 'VIRTUAL'];
        $estadosRevision = ['APROBADO', 'EN_REVISION', 'RECHAZADO', 'PENDIENTE'];
        $filtrosAplicados = $this->filtrosAplicadosReportes($request, $carreras, $grupos, $docentes);

        $postulantes = $this->consultaPostulantesFiltrados($request)
            ->orderBy('id', 'asc')
            ->get();

        $resumen = [
            'total_postulantes' => $postulantes->count(),
            'total_con_notas' => $postulantes->filter(fn ($postulante) => !is_null($postulante->notas))->count(),
            'total_aprobados' => $postulantes->filter(fn ($postulante) => optional($postulante->notas)->estado === 'APROBADO')->count(),
            'total_reprobados' => $postulantes->filter(fn ($postulante) => optional($postulante->notas)->estado === 'REPROBADO')->count(),
            'total_admitidos' => $postulantes->where('estado_admision', 'ADMITIDO')->count(),
            'total_no_admitidos' => $postulantes->where('estado_admision', 'NO_ADMITIDO')->count(),
            'total_pendientes' => $postulantes->filter(fn ($postulante) => ($postulante->estado_admision ?? 'PENDIENTE') === 'PENDIENTE')->count(),
            'total_requisitos_aprobados' => $postulantes->where('estado_requisitos', 'APROBADO')->count(),
            'total_pago_aprobado' => $postulantes->where('estado_pago_revision', 'APROBADO')->count(),
        ];

        $reporteCarreras = $carreras->map(function ($carrera) use ($postulantes) {
            $admitidos = $postulantes
                ->where('carrera_asignada_id', $carrera->id)
                ->where('estado_admision', 'ADMITIDO')
                ->count();

            return [
                'carrera' => $carrera->nombre,
                'cupo_maximo' => $carrera->cupo_maximo,
                'cupos_ocupados' => $carrera->cupos_ocupados,
                'postulantes_admitidos' => $admitidos,
                'cupos_disponibles' => max(0, $carrera->cupo_maximo - $carrera->cupos_ocupados),
            ];
        });

        $reporteGrupos = $grupos->map(function ($grupo) use ($postulantes) {
            $postulantesGrupo = $postulantes->where('grupo_id', $grupo->id);
            $conNotas = $postulantesGrupo->filter(fn ($postulante) => !is_null($postulante->notas));

            return [
                'grupo' => $grupo->nombre_grupo,
                'total_postulantes' => $postulantesGrupo->count(),
                'aprobados' => $postulantesGrupo->filter(fn ($postulante) => optional($postulante->notas)->estado === 'APROBADO')->count(),
                'reprobados' => $postulantesGrupo->filter(fn ($postulante) => optional($postulante->notas)->estado === 'REPROBADO')->count(),
                'admitidos' => $postulantesGrupo->where('estado_admision', 'ADMITIDO')->count(),
                'promedio_general' => $conNotas->count() > 0
                    ? $conNotas->avg(fn ($postulante) => (float) $postulante->notas->promedio)
                    : null,
            ];
        });

        $reporteDocentes = $this->consultaAsignacionesFiltradas($request)
            ->get()
            ->groupBy(fn ($asignacion) => $asignacion->docente_id . '|' . $asignacion->materia)
            ->map(function ($asignacionesGrupo) use ($postulantes) {
                $primeraAsignacion = $asignacionesGrupo->first();
                $grupoIds = $asignacionesGrupo->pluck('grupo_id')->unique()->values();
                $postulantesVinculados = $postulantes->whereIn('grupo_id', $grupoIds->all());
                $aprobados = $postulantesVinculados->filter(fn ($postulante) => optional($postulante->notas)->estado === 'APROBADO')->count();
                $total = $postulantesVinculados->count();

                return [
                    'docente' => trim(($primeraAsignacion->docente->nombres ?? 'Sin docente') . ' ' . ($primeraAsignacion->docente->apellidos ?? '')),
                    'materia' => $primeraAsignacion->materia,
                    'grupos_asignados' => $asignacionesGrupo->pluck('grupo.nombre_grupo')->filter()->unique()->implode(', '),
                    'postulantes_vinculados' => $total,
                    'aprobados' => $aprobados,
                    'porcentaje_aprobados' => $total > 0 ? ($aprobados * 100) / $total : 0,
                ];
            })
            ->values();

        return compact(
            'carreras',
            'grupos',
            'docentes',
            'carrerasPostulantes',
            'materias',
            'turnos',
            'modalidades',
            'estadosRevision',
            'postulantes',
            'resumen',
            'reporteCarreras',
            'reporteGrupos',
            'reporteDocentes',
            'filtrosAplicados'
        );
    }

    private function consultaPostulantesFiltrados(Request $request)
    {
        $query = Postulante::with(['grupo.asignaciones.horarios', 'notas', 'carreraAsignada']);

        if ($request->filled('carrera_1')) {
            $query->where('carrera_1', $request->carrera_1);
        }

        if ($request->filled('carrera_asignada_id')) {
            $query->where('carrera_asignada_id', $request->carrera_asignada_id);
        }

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }

        if ($request->filled('estado_academico')) {
            if ($request->estado_academico === 'SIN_NOTAS') {
                $query->whereDoesntHave('notas');
            } else {
                $query->whereHas('notas', function ($subquery) use ($request) {
                    $subquery->where('estado', $request->estado_academico);
                });
            }
        }

        if ($request->filled('estado_admision')) {
            if ($request->estado_admision === 'PENDIENTE') {
                $query->where(function ($subquery) {
                    $subquery->whereNull('estado_admision')
                        ->orWhere('estado_admision', 'PENDIENTE');
                });
            } else {
                $query->where('estado_admision', $request->estado_admision);
            }
        }

        if ($request->filled('estado_requisitos')) {
            if ($request->estado_requisitos === 'PENDIENTE') {
                $query->where(function ($subquery) {
                    $subquery->whereNull('estado_requisitos')
                        ->orWhere('estado_requisitos', 'PENDIENTE');
                });
            } else {
                $query->where('estado_requisitos', $request->estado_requisitos);
            }
        }

        if ($request->filled('estado_pago_revision')) {
            if ($request->estado_pago_revision === 'PENDIENTE') {
                $query->where(function ($subquery) {
                    $subquery->whereNull('estado_pago_revision')
                        ->orWhere('estado_pago_revision', 'PENDIENTE');
                });
            } else {
                $query->where('estado_pago_revision', $request->estado_pago_revision);
            }
        }

        if ($request->filled('docente_id')) {
            $query->whereHas('grupo.asignaciones', function ($subquery) use ($request) {
                $subquery->where('docente_id', $request->docente_id);
            });
        }

        if ($request->filled('materia')) {
            $query->whereHas('grupo.asignaciones', function ($subquery) use ($request) {
                $subquery->where('materia', $request->materia);
            });
        }

        if ($request->filled('turno')) {
            $query->whereHas('grupo.asignaciones.horarios', function ($subquery) use ($request) {
                $subquery->where('turno', $request->turno);
            });
        }

        if ($request->filled('modalidad')) {
            $query->whereHas('grupo.asignaciones.horarios', function ($subquery) use ($request) {
                $subquery->where('modalidad', $request->modalidad);
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        return $query;
    }

    private function consultaAsignacionesFiltradas(Request $request)
    {
        $query = AsignacionDocente::with(['docente', 'grupo', 'horarios'])
            ->where('estado', 'ACTIVA');

        if ($request->filled('docente_id')) {
            $query->where('docente_id', $request->docente_id);
        }

        if ($request->filled('materia')) {
            $query->where('materia', $request->materia);
        }

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }

        if ($request->filled('turno')) {
            $query->whereHas('horarios', function ($subquery) use ($request) {
                $subquery->where('turno', $request->turno);
            });
        }

        if ($request->filled('modalidad')) {
            $query->whereHas('horarios', function ($subquery) use ($request) {
                $subquery->where('modalidad', $request->modalidad);
            });
        }

        return $query;
    }

    private function filtrosAplicadosReportes(Request $request, $carreras, $grupos, $docentes): array
    {
        $filtros = [];
        $mapa = [
            'carrera_1' => 'Carrera 1',
            'estado_academico' => 'Estado academico',
            'estado_admision' => 'Estado admision',
            'estado_requisitos' => 'Estado requisitos',
            'estado_pago_revision' => 'Estado pago',
            'materia' => 'Materia',
            'turno' => 'Turno',
            'modalidad' => 'Modalidad',
            'fecha_desde' => 'Fecha desde',
            'fecha_hasta' => 'Fecha hasta',
        ];

        foreach ($mapa as $campo => $etiqueta) {
            if ($request->filled($campo)) {
                $filtros[$etiqueta] = str_replace('_', ' ', $request->input($campo));
            }
        }

        if ($request->filled('carrera_asignada_id')) {
            $filtros['Carrera asignada'] = optional($carreras->firstWhere('id', (int) $request->carrera_asignada_id))->nombre
                ?? $request->carrera_asignada_id;
        }

        if ($request->filled('grupo_id')) {
            $filtros['Grupo'] = optional($grupos->firstWhere('id', (int) $request->grupo_id))->nombre_grupo
                ?? $request->grupo_id;
        }

        if ($request->filled('docente_id')) {
            $docente = $docentes->firstWhere('id', (int) $request->docente_id);
            $filtros['Docente'] = $docente
                ? trim($docente->nombres . ' ' . $docente->apellidos)
                : $request->docente_id;
        }

        return $filtros;
    }

    private function descargarCsv(string $filename, array $headers, $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            echo "\xEF\xBB\xBF";
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function textoEstadoRevision(?string $estado): string
    {
        return match ($estado ?? 'PENDIENTE') {
            'EN_REVISION' => 'EN REVISIÓN',
            'APROBADO' => 'APROBADO',
            'RECHAZADO' => 'RECHAZADO',
            default => 'PENDIENTE',
        };
    }
    private function docenteActual(): ?Docente
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return null;
        }

        return Docente::where('user_id', $usuario->id)
            ->orWhere('correo', $usuario->email)
            ->first();
    }

    private function normalizarMateria(string $materia): string
    {
        $clave = mb_strtolower(trim($materia));
        $clave = str_replace(
            ['ã³', 'ó', 'ã¡', 'á', 'ã©', 'é', 'ã­', 'í'],
            ['ó', 'ó', 'á', 'á', 'é', 'é', 'í', 'í'],
            $clave
        );

        return match ($clave) {
            'computacion', 'computación' => 'Computación',
            'matematicas', 'matemáticas' => 'Matemáticas',
            'ingles', 'inglés' => 'Inglés',
            'fisica', 'física' => 'Física',
            default => trim($materia),
        };
    }

    private function columnasMateria(string $materia): ?array
    {
        return match ($this->normalizarMateria($materia)) {
            'Computación' => ['computacion_1', 'computacion_2', 'computacion_3'],
            'Matemáticas' => ['matematicas_1', 'matematicas_2', 'matematicas_3'],
            'Inglés' => ['ingles_1', 'ingles_2', 'ingles_3'],
            'Física' => ['fisica_1', 'fisica_2', 'fisica_3'],
            default => null,
        };
    }

    private function totalesMaterias(Nota $nota): array
    {
        return [
            'Computación' => (float) $nota->computacion_1 + (float) $nota->computacion_2 + (float) $nota->computacion_3,
            'Matemáticas' => (float) $nota->matematicas_1 + (float) $nota->matematicas_2 + (float) $nota->matematicas_3,
            'Inglés' => (float) $nota->ingles_1 + (float) $nota->ingles_2 + (float) $nota->ingles_3,
            'Física' => (float) $nota->fisica_1 + (float) $nota->fisica_2 + (float) $nota->fisica_3,
        ];
    }
    private function ordenDia(?string $dia): int
    {
        return match ($dia) {
            'Lunes' => 1,
            'Martes' => 2,
            'Miércoles' => 3,
            'Jueves' => 4,
            'Viernes' => 5,
            'Sábado' => 6,
            default => 99,
        };
    }

    private function bloquesPorTurno(string $turno): array
    {
        return match ($turno) {
            'TARDE' => [['13:15', '14:30'], ['14:40', '15:55'], ['16:10', '17:25'], ['17:35', '18:50']],
            'NOCHE' => [['16:30', '17:45'], ['17:55', '19:10'], ['19:20', '20:35'], ['20:45', '21:50']],
            default => [['07:30', '08:45'], ['08:55', '10:10'], ['10:25', '11:40'], ['11:50', '13:05']],
        };
    }

    private function validarCruceHorario(AsignacionDocente $asignacion, ?Aula $aula, string $dia, string $horaInicio, string $horaFin): ?string
    {
        if ($aula) {
            $choqueAula = HorarioClase::where('aula_id', $aula->id)
                ->where('dia', $dia)
                ->where('estado', 'ACTIVO')
                ->where(function ($query) use ($horaInicio, $horaFin) {
                    $query->where('hora_inicio', '<', $horaFin)
                        ->where('hora_fin', '>', $horaInicio);
                })
                ->exists();

            if ($choqueAula) {
                return 'El aula ya está ocupada en ' . $dia . ' de ' . $horaInicio . ' a ' . $horaFin . '.';
            }
        }

        $choqueDocente = HorarioClase::whereHas('asignacionDocente', function ($query) use ($asignacion) {
                $query->where('docente_id', $asignacion->docente_id);
            })
            ->where('dia', $dia)
            ->where('estado', 'ACTIVO')
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where('hora_inicio', '<', $horaFin)
                    ->where('hora_fin', '>', $horaInicio);
            })
            ->exists();

        if ($choqueDocente) {
            return 'El docente ya tiene una clase asignada en ' . $dia . ' de ' . $horaInicio . ' a ' . $horaFin . '.';
        }

        return null;
    }

    private function siguienteNombreGrupo(string $turno): string
    {
        $cantidad = Grupo::where('turno', $turno)->count() + 1;

        return $this->formatearNombreGrupo($turno, $cantidad);
    }

    private function formatearNombreGrupo(string $turno, int $numero): string
    {
        $prefijo = match ($turno) {
            'TARDE' => 'T',
            'NOCHE' => 'N',
            default => 'M',
        };

        return $prefijo . str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
    }
    public function mostrarBitacora(Request $request)
    {
        $query = Bitacora::query()->orderByDesc('created_at');

        if ($request->filled('usuario')) {
            $query->where('nombre_usuario', 'like', '%' . $request->usuario . '%');
        }

        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->filled('accion')) {
            $query->where('accion', 'like', '%' . $request->accion . '%');
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $bitacoras = $query->paginate(20)->withQueryString();
        $modulos = Bitacora::select('modulo')->distinct()->orderBy('modulo')->pluck('modulo');
        $roles = Bitacora::select('rol')->whereNotNull('rol')->distinct()->orderBy('rol')->pluck('rol');

        return view('modules.seguridad_usuarios.bitacora', compact('bitacoras', 'modulos', 'roles'));
    }
    public function mostrarImportarUsuarios()
    {
        return view('importar_usuarios');
    }

    public function importarUsuarios(Request $request)
    {
        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $creados = 0;
        $omitidos = 0;
        $errores = [];
        $rolesPermitidos = ['admin', 'coordinador', 'docente', 'postulante'];
        $handle = fopen($request->file('archivo_csv')->getRealPath(), 'r');
        $headers = fgetcsv($handle, 0, ',');

        if (!$headers) {
            return redirect()->back()->withErrors(['archivo_csv' => 'El archivo CSV está vacío.']);
        }

        $headers = array_map(fn ($header) => trim(strtolower($header)), $headers);

        while (($fila = fgetcsv($handle, 0, ',')) !== false) {
            $datos = array_combine($headers, array_pad($fila, count($headers), null));

            if (!$datos || empty($datos['email'])) {
                $errores[] = 'Fila omitida: falta email.';
                continue;
            }

            $role = strtolower(trim($datos['role'] ?? 'postulante'));

            if (!in_array($role, $rolesPermitidos, true)) {
                $errores[] = 'Rol no permitido para ' . $datos['email'] . '.';
                continue;
            }

            if (User::where('email', $datos['email'])->exists()) {
                $omitidos++;
                continue;
            }

            $password = trim($datos['password'] ?? '');

            if (strlen($password) < 6) {
                $errores[] = 'Contraseña inválida para ' . $datos['email'] . '.';
                continue;
            }

            $usuario = User::create([
                'name' => trim($datos['name'] ?? $datos['email']),
                'email' => trim($datos['email']),
                'password' => Hash::make($password),
                'role' => $role,
            ]);

            if ($role === 'docente') {
                Docente::where('correo', $usuario->email)
                    ->whereNull('user_id')
                    ->update(['user_id' => $usuario->id]);
            }

            $creados++;
        }

        fclose($handle);

        BitacoraService::registrar(
            'Importación CSV de usuarios',
            'Seguridad y usuarios',
            'Resultado: creados ' . $creados . ', omitidos ' . $omitidos . ', errores ' . count($errores) . '.',
            $request
        );

        return redirect()->back()->with('resultado_importacion', compact('creados', 'omitidos', 'errores'));
    }

    public function mostrarPanelDocente()
    {
        $docenteActual = $this->docenteActual();
        $esAdmin = in_array(auth()->user()->role, ['admin', 'coordinador'], true);

        $asignaciones = AsignacionDocente::with(['docente', 'grupo.postulantes', 'horarios.aula'])
            ->where('estado', 'ACTIVA')
            ->when(!$esAdmin, fn ($query) => $query->where('docente_id', optional($docenteActual)->id))
            ->orderBy('grupo_id')
            ->orderBy('materia')
            ->get();

        $asistencias = AsistenciaDocente::with(['docente', 'asignacionDocente.grupo'])
            ->when(!$esAdmin, fn ($query) => $query->where('docente_id', optional($docenteActual)->id))
            ->orderByDesc('fecha')
            ->limit(50)
            ->get();

        return view('docente_panel', compact('docenteActual', 'asignaciones', 'asistencias', 'esAdmin'));
    }

    public function registrarAsistenciaDocente(Request $request)
    {
        $docente = $this->docenteActual();

        if (!$docente) {
            return redirect()->back()->withErrors(['docente' => 'No se encontró un docente vinculado a su usuario.']);
        }

        $request->validate([
            'asignacion_docente_id' => 'required|exists:asignaciones_docentes,id',
            'fecha' => 'required|date',
            'estado' => 'required|in:PRESENTE,AUSENTE,LICENCIA',
            'observacion' => 'nullable|string|max:500',
        ]);

        $asignacion = AsignacionDocente::where('id', $request->asignacion_docente_id)
            ->where('docente_id', $docente->id)
            ->where('estado', 'ACTIVA')
            ->firstOrFail();

        AsistenciaDocente::updateOrCreate(
            [
                'docente_id' => $docente->id,
                'asignacion_docente_id' => $asignacion->id,
                'fecha' => $request->fecha,
            ],
            [
                'estado' => $request->estado,
                'observacion' => $request->observacion,
            ]
        );

        BitacoraService::registrar(
            'Registro de asistencia docente',
            'Docentes y carga horaria',
            'Se registró asistencia del docente #' . $docente->id . ' para la asignación #' . $asignacion->id . '.',
            $request
        );

        return redirect()->back()->with('success', 'Asistencia docente registrada correctamente.');
    }

    public function registrarHorarioIntensivo(Request $request)
    {
        $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'turno' => 'required|in:MAÑANA,TARDE,NOCHE',
            'modalidad' => 'required|in:PRESENCIAL,VIRTUAL',
            'aula_id' => 'nullable|required_if:modalidad,PRESENCIAL|exists:aulas,id',
            'examen_presencial' => 'nullable|boolean',
            'observacion_horario' => 'nullable|string|max:500',
        ]);

        $grupo = Grupo::findOrFail($request->grupo_id);
        $asignaciones = AsignacionDocente::with(['docente', 'grupo'])
            ->where('grupo_id', $grupo->id)
            ->where('estado', 'ACTIVA')
            ->orderBy('materia')
            ->get();

        if ($asignaciones->count() < 4) {
            return redirect()->back()->withErrors([
                'grupo_id' => 'El grupo debe tener asignadas las 4 materias antes de generar horario intensivo.',
            ])->withInput();
        }

        $aula = $request->filled('aula_id') ? Aula::findOrFail($request->aula_id) : null;
        $bloques = $this->bloquesPorTurno($request->turno);
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $creados = 0;

        try {
            DB::transaction(function () use ($dias, $bloques, $asignaciones, $request, $aula, &$creados, $grupo) {
                foreach ($dias as $indiceDia => $dia) {
                    $asignacionesRotadas = $asignaciones->values();

                    for ($i = 0; $i < $indiceDia; $i++) {
                        $asignacionesRotadas->push($asignacionesRotadas->shift());
                    }

                    foreach ($bloques as $indiceBloque => $bloque) {
                        $asignacion = $asignacionesRotadas[$indiceBloque];
                        $error = $this->validarCruceHorario($asignacion, $aula, $dia, $bloque[0], $bloque[1]);

                        if ($error) {
                            throw new \RuntimeException($error);
                        }

                        HorarioClase::firstOrCreate([
                            'asignacion_docente_id' => $asignacion->id,
                            'dia' => $dia,
                            'hora_inicio' => $bloque[0],
                            'hora_fin' => $bloque[1],
                        ], [
                            'aula_id' => $request->filled('aula_id') ? $request->aula_id : null,
                            'turno' => $request->turno,
                            'modalidad' => $request->modalidad,
                            'examen_presencial' => $request->boolean('examen_presencial'),
                            'observacion_horario' => $request->observacion_horario,
                            'estado' => 'ACTIVO',
                        ]);

                        $creados++;
                    }
                }

                $grupo->update(['turno' => $request->turno]);
            });
        } catch (\RuntimeException $exception) {
            return redirect()->back()->withErrors([
                'horario_intensivo' => $exception->getMessage(),
            ])->withInput();
        }

        BitacoraService::registrar(
            'Registro de horario',
            'Gestión académica CUP',
            'Se generó horario intensivo semanal para el grupo #' . $grupo->id . '.',
            $request
        );

        return redirect('/horarios-clases')->with('success', "Horario intensivo semanal generado. Bloques procesados: {$creados}.");
    }

    public function normalizarNomenclaturaGrupos()
    {
        $contadores = ['MAÑANA' => 0, 'TARDE' => 0, 'NOCHE' => 0];

        Grupo::orderBy('id')->get()->each(function ($grupo) use (&$contadores) {
            $turno = $grupo->turno ?: 'MAÑANA';
            $contadores[$turno] = ($contadores[$turno] ?? 0) + 1;
            $grupo->update([
                'turno' => $turno,
                'nombre_grupo' => $this->formatearNombreGrupo($turno, $contadores[$turno]),
            ]);
        });

        return redirect()->back()->with('success', 'Nomenclatura de grupos actualizada por turno.');
    }
    // Muestra el formulario y listado de docentes
    public function mostrarDocentes()
    {
        $docentes = Docente::with('competencias')->orderBy('id', 'asc')->get();

        return view('docentes', compact('docentes'));
    }

    // Registra un nuevo docente
    public function registrarDocente(Request $request)
    {
        $request->validate([
            'ci' => 'required|string|unique:docentes,ci',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => 'nullable|email|unique:docentes,correo',
            'telefono' => 'nullable|string|max:30',
            'profesion' => 'required|string|max:255',
            'es_profesional' => 'nullable',
            'tiene_maestria' => 'nullable',
            'tiene_diplomado' => 'nullable',
        ]);

        $esProfesional = $request->has('es_profesional');
        $tieneMaestria = $request->has('tiene_maestria');
        $tieneDiplomado = $request->has('tiene_diplomado');

        $estado = ($esProfesional && $tieneMaestria && $tieneDiplomado)
            ? 'HABILITADO'
            : 'PENDIENTE';

        $usuarioDocente = $request->filled('correo')
            ? User::where('email', $request->correo)->where('role', 'docente')->first()
            : null;

        $docente = Docente::create([
            'user_id' => optional($usuarioDocente)->id,
            'ci' => $request->ci,
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'profesion' => $request->profesion,
            'es_profesional' => $esProfesional,
            'tiene_maestria' => $tieneMaestria,
            'tiene_diplomado' => $tieneDiplomado,
            'estado' => $estado,
        ]);

        BitacoraService::registrar(
            'Creación de docente',
            'Docentes y carga horaria',
            'Se registró el docente #' . $docente->id . ' ' . $docente->nombres . ' ' . $docente->apellidos . '.',
            $request
        );

        return redirect('/docentes')->with('success', 'Docente registrado correctamente.');
    }

    public function guardarMisDocumentosDocente(Request $request)
    {
        $docente = $this->docenteActual();

        if (!$docente) {
            return redirect()->back()->withErrors(['docente' => 'No se encontró un docente vinculado a su usuario.']);
        }

        return $this->guardarDocumentosDocente($request, $docente);
    }
    public function guardarDocumentosDocente(Request $request, Docente $docente)
    {
        $request->validate([
            'archivo_titulo_profesional' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_curriculum' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_experiencia_docente' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_certificado_capacitacion' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_certificado_idioma' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_otro_respaldo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $datos = [];

        foreach ([
            'archivo_titulo_profesional',
            'archivo_curriculum',
            'archivo_experiencia_docente',
            'archivo_certificado_capacitacion',
            'archivo_certificado_idioma',
            'archivo_otro_respaldo',
        ] as $campo) {
            if ($request->hasFile($campo)) {
                $datos[$campo] = $request->file($campo)->store('docentes/' . $docente->id, 'public');
            }
        }

        if (empty($datos)) {
            return redirect()->back()->withErrors(['documentos_docente' => 'Debe seleccionar al menos un archivo.']);
        }

        $datos['estado_documentos_docente'] = 'EN_REVISION';
        $datos['observacion_documentos_docente'] = 'Documentos cargados para revisión.';
        $docente->update($datos);

        BitacoraService::registrar(
            'Carga de documentos docentes',
            'Docentes y carga horaria',
            'Se cargaron documentos del docente #' . $docente->id . '.',
            $request
        );

        return redirect()->back()->with('success', 'Documentos del docente cargados correctamente.');
    }

    public function aprobarDocumentosDocente(Request $request, Docente $docente)
    {
        $docente->update([
            'estado_documentos_docente' => 'APROBADO',
            'observacion_documentos_docente' => 'Documentos docentes aprobados por coordinación.',
        ]);

        BitacoraService::registrar(
            'Aprobación de documentos docentes',
            'Docentes y carga horaria',
            'Se aprobaron documentos del docente #' . $docente->id . '.',
            $request
        );

        return redirect()->back()->with('success', 'Documentos del docente aprobados.');
    }

    public function rechazarDocumentosDocente(Request $request, Docente $docente)
    {
        $request->validate([
            'observacion_documentos_docente' => 'required|string|min:5|max:500',
        ]);

        $docente->update([
            'estado_documentos_docente' => 'RECHAZADO',
            'observacion_documentos_docente' => $request->observacion_documentos_docente,
        ]);

        BitacoraService::registrar(
            'Rechazo de documentos docentes',
            'Docentes y carga horaria',
            'Se rechazaron documentos del docente #' . $docente->id . '.',
            $request
        );

        return redirect()->back()->with('success', 'Documentos del docente rechazados.');
    }

    public function guardarCompetenciaDocente(Request $request, Docente $docente)
    {
        $request->validate([
            'materia' => 'required|in:Computación,Matemáticas,Inglés,Física',
            'tipo_respaldo' => 'required|in:TITULO,EXPERIENCIA,CAPACITACION,CERTIFICACION,OTRO',
            'descripcion' => 'nullable|string|max:1000',
            'archivo_respaldo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $datos = [
            'docente_id' => $docente->id,
            'materia' => $request->materia,
            'tipo_respaldo' => $request->tipo_respaldo,
            'descripcion' => $request->descripcion,
            'estado' => 'PENDIENTE',
        ];

        if ($request->hasFile('archivo_respaldo')) {
            $datos['archivo_respaldo'] = $request->file('archivo_respaldo')->store('docentes/' . $docente->id . '/competencias', 'public');
        }

        $competencia = DocenteCompetencia::create($datos);

        BitacoraService::registrar(
            'Registro de competencia docente',
            'Docentes y carga horaria',
            'Se registró competencia #' . $competencia->id . ' para ' . $request->materia . '.',
            $request
        );

        return redirect()->back()->with('success', 'Competencia docente registrada para revisión.');
    }

    public function aprobarCompetenciaDocente(Request $request, DocenteCompetencia $competencia)
    {
        $competencia->update([
            'estado' => 'APROBADO',
            'observacion' => 'Competencia aprobada por coordinación.',
        ]);

        BitacoraService::registrar(
            'Aprobación de competencia docente',
            'Docentes y carga horaria',
            'Se aprobó competencia #' . $competencia->id . ' para ' . $competencia->materia . '.',
            $request
        );

        return redirect()->back()->with('success', 'Competencia docente aprobada.');
    }

    public function rechazarCompetenciaDocente(Request $request, DocenteCompetencia $competencia)
    {
        $request->validate([
            'observacion' => 'required|string|min:5|max:500',
        ]);

        $competencia->update([
            'estado' => 'RECHAZADO',
            'observacion' => $request->observacion,
        ]);

        BitacoraService::registrar(
            'Rechazo de competencia docente',
            'Docentes y carga horaria',
            'Se rechazó competencia #' . $competencia->id . ' para ' . $competencia->materia . '.',
            $request
        );

        return redirect()->back()->with('success', 'Competencia docente rechazada.');
    }
    // Muestra el formulario y listado de asignaciones de docentes
    public function mostrarAsignacionesDocentes()
    {
        $docentes = Docente::with(['competencias' => function ($query) {
                $query->where('estado', 'APROBADO');
            }])
            ->where('estado', 'HABILITADO')
            ->orderBy('nombres', 'asc')
            ->get();

        $grupos = Grupo::orderBy('id', 'asc')->get();

        $asignaciones = AsignacionDocente::with(['docente', 'grupo'])
            ->orderBy('id', 'asc')
            ->get();

        return view('asignaciones_docentes', compact('docentes', 'grupos', 'asignaciones'));
    }

    // Registra la asignación de un docente habilitado a un grupo y materia
    public function registrarAsignacionDocente(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:docentes,id',
            'grupo_id' => 'required|exists:grupos,id',
            'materia' => 'required|string',
        ]);

        $docente = Docente::findOrFail($request->docente_id);
        $materia = $this->normalizarMateria($request->materia);

        if (!$this->columnasMateria($materia)) {
            return redirect()->back()->withErrors([
                'materia' => 'La materia seleccionada no es válida.'
            ])->withInput();
        }

        if ($docente->estado !== 'HABILITADO') {
            return redirect()->back()->withErrors([
                'docente_id' => 'Solo se pueden asignar docentes habilitados.'
            ])->withInput();
        }

        $tieneCompetenciaAprobada = DocenteCompetencia::where('docente_id', $docente->id)
            ->where('materia', $materia)
            ->where('estado', 'APROBADO')
            ->exists();

        if (!$tieneCompetenciaAprobada) {
            return redirect()->back()->withErrors([
                'materia' => 'El docente no tiene competencia aprobada para impartir esta materia.'
            ])->withInput();
        }

        $cantidadGruposAsignados = AsignacionDocente::where('docente_id', $request->docente_id)
            ->where('estado', 'ACTIVA')
            ->distinct('grupo_id')
            ->count('grupo_id');

        $yaTieneEsteGrupo = AsignacionDocente::where('docente_id', $request->docente_id)
            ->where('grupo_id', $request->grupo_id)
            ->where('estado', 'ACTIVA')
            ->exists();

        if (!$yaTieneEsteGrupo && $cantidadGruposAsignados >= 4) {
            return redirect()->back()->withErrors([
                'docente_id' => 'El docente ya tiene el máximo de 4 grupos asignados.'
            ])->withInput();
        }

        $materiaYaAsignada = AsignacionDocente::where('grupo_id', $request->grupo_id)
            ->where('materia', $materia)
            ->where('estado', 'ACTIVA')
            ->exists();

        if ($materiaYaAsignada) {
            return redirect()->back()->withErrors([
                'materia' => 'Esta materia ya tiene un docente asignado en este grupo.'
            ])->withInput();
        }

        AsignacionDocente::create([
            'docente_id' => $request->docente_id,
            'grupo_id' => $request->grupo_id,
            'materia' => $materia,
            'estado' => 'ACTIVA',
        ]);

        BitacoraService::registrar(
            'Asignación de docente',
            'Docentes y carga horaria',
            'Se asignó docente #' . $request->docente_id . ' al grupo #' . $request->grupo_id . ' en ' . $materia . '.',
            $request
        );

        return redirect('/asignaciones-docentes')->with('success', 'Docente asignado correctamente al grupo.');
    }

    // Muestra el formulario y listado de aulas
    public function mostrarAulas()
    {
        $aulas = Aula::orderBy('id', 'asc')->get();

        return view('aulas', compact('aulas'));
    }

    // Registra una nueva aula
    public function registrarAula(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:255|unique:aulas,codigo',
            'pabellon' => 'nullable|string|max:255',
            'capacidad' => 'required|integer|min:1',
            'estado' => 'required|string|in:DISPONIBLE,NO DISPONIBLE',
        ]);

        Aula::create([
            'codigo' => $request->codigo,
            'pabellon' => $request->pabellon,
            'capacidad' => $request->capacidad,
            'estado' => $request->estado,
        ]);

        return redirect('/aulas')->with('success', 'Aula registrada correctamente.');
    }

    // Muestra el formulario y listado de horarios de clases
    public function mostrarHorariosClases()
    {
        $asignaciones = AsignacionDocente::with(['docente', 'grupo'])
            ->where('estado', 'ACTIVA')
            ->orderBy('id', 'asc')
            ->get();

        $aulas = Aula::where('estado', 'DISPONIBLE')
            ->orderBy('codigo', 'asc')
            ->get();

        $grupos = Grupo::orderBy('id', 'asc')->get();

        $horarios = HorarioClase::with(['asignacionDocente.docente', 'asignacionDocente.grupo', 'aula'])
            ->orderBy('id', 'asc')
            ->get();

        return view('horarios_clases', compact('asignaciones', 'aulas', 'grupos', 'horarios'));
    }

    // Registra un horario validando modalidad, aula y cruces de horario
    public function registrarHorarioClase(Request $request)
    {
        $request->validate([
            'asignacion_docente_id' => 'required|exists:asignaciones_docentes,id',
            'aula_id' => 'nullable|required_if:modalidad,PRESENCIAL|exists:aulas,id',
            'dia' => 'required|string|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'turno' => 'required|string|in:MAÃƒÆ’Ã¢â‚¬�‹Å“ANA,TARDE,NOCHE',
            'modalidad' => 'required|string|in:PRESENCIAL,VIRTUAL',
            'examen_presencial' => 'nullable|boolean',
            'observacion_horario' => 'nullable|string|max:500',
        ], [
            'aula_id.required_if' => 'Debe seleccionar un aula cuando la modalidad es presencial.',
            'hora_fin.after' => 'La hora fin debe ser posterior a la hora inicio.',
            'turno.required' => 'Debe seleccionar un turno.',
            'modalidad.required' => 'Debe seleccionar una modalidad.',
        ]);

        $asignacion = AsignacionDocente::with(['docente', 'grupo'])
            ->findOrFail($request->asignacion_docente_id);

        $aula = null;

        if ($request->filled('aula_id')) {
            $aula = Aula::findOrFail($request->aula_id);

            if ($aula->estado !== 'DISPONIBLE') {
                return redirect()->back()->withErrors([
                    'aula_id' => 'El aula seleccionada no está disponible.'
                ])->withInput();
            }

            if ($aula->capacidad < $asignacion->grupo->cupo_maximo) {
                return redirect()->back()->withErrors([
                    'aula_id' => 'El aula no tiene capacidad suficiente para este grupo.'
                ])->withInput();
            }

            $choqueAula = HorarioClase::where('aula_id', $aula->id)
                ->where('dia', $request->dia)
                ->where('estado', 'ACTIVO')
                ->where(function ($query) use ($request) {
                    $query->where('hora_inicio', '<', $request->hora_fin)
                        ->where('hora_fin', '>', $request->hora_inicio);
                })
                ->exists();

            if ($choqueAula) {
                return redirect()->back()->withErrors([
                    'aula_id' => 'El aula ya está ocupada en ese día y rango de horario.'
                ])->withInput();
            }
        }

        $choqueDocente = HorarioClase::whereHas('asignacionDocente', function ($query) use ($asignacion) {
                $query->where('docente_id', $asignacion->docente_id);
            })
            ->where('dia', $request->dia)
            ->where('estado', 'ACTIVO')
            ->where(function ($query) use ($request) {
                $query->where('hora_inicio', '<', $request->hora_fin)
                    ->where('hora_fin', '>', $request->hora_inicio);
            })
            ->exists();

        if ($choqueDocente) {
            return redirect()->back()->withErrors([
                'asignacion_docente_id' => 'El docente ya tiene una clase asignada en ese mismo día y horario.'
            ])->withInput();
        }

        $horario = HorarioClase::create([
            'asignacion_docente_id' => $request->asignacion_docente_id,
            'aula_id' => $request->filled('aula_id') ? $request->aula_id : null,
            'dia' => $request->dia,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'turno' => $request->turno,
            'modalidad' => $request->modalidad,
            'examen_presencial' => $request->boolean('examen_presencial'),
            'observacion_horario' => $request->observacion_horario,
            'estado' => 'ACTIVO',
        ]);

        BitacoraService::registrar(
            'Registro de horario',
            'Gestión académica CUP',
            'Se registró horario #' . $horario->id . ' para la asignación #' . $request->asignacion_docente_id . '.',
            $request
        );

        return redirect('/horarios-clases')->with('success', 'Horario de clase registrado correctamente.');
    }

}







