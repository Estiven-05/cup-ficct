<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Docente;
use App\Services\BitacoraService;

class AuthController extends Controller
{
    public function mostrarLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            BitacoraService::registrar(
                'Bloqueo de login por intentos fallidos',
                'Seguridad y usuarios',
                'Cuenta bloqueada temporalmente para el correo ' . $request->input('email') . '.',
                $request,
                null,
                $request->input('email'),
                'invitado'
            );

            return $this->respuestaLoginBloqueado($request, $throttleKey);
        }

        if (Auth::attempt($credenciales)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $usuario = Auth::user();

            BitacoraService::registrar(
                'Inicio de sesión exitoso',
                'Seguridad y usuarios',
                'El usuario inició sesión correctamente.',
                $request,
                $usuario
            );

            if ($usuario->role === 'admin') {
                return redirect('/dashboard');
            }

            if ($usuario->role === 'docente') {
                return redirect('/docente-panel');
            }

            if ($usuario->role === 'coordinador') {
                return redirect('/dashboard');
            }

            if ($usuario->role === 'postulante') {
                return redirect('/mi-resultado');
            }

            return redirect('/dashboard');
        }

        RateLimiter::hit($throttleKey, 600);

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            BitacoraService::registrar(
                'Bloqueo de login por intentos fallidos',
                'Seguridad y usuarios',
                'Cuenta bloqueada temporalmente para el correo ' . $request->input('email') . '.',
                $request,
                null,
                $request->input('email'),
                'invitado'
            );

            return $this->respuestaLoginBloqueado($request, $throttleKey);
        }

        BitacoraService::registrar(
            'Intento fallido de login',
            'Seguridad y usuarios',
            'Intento fallido para el correo ' . $request->input('email') . '.',
            $request,
            null,
            $request->input('email'),
            'invitado'
        );

        return back()->withErrors([
            'email' => 'Correo o contraseña incorrectos.',
        ])->onlyInput('email');
    }


    public function mostrarRecuperarPassword()
    {
        return view('password_recuperar');
    }

    public function procesarRecuperarPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No existe una cuenta registrada con ese correo.',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => hash('sha256', $token),
                'created_at' => now(),
            ]
        );

        BitacoraService::registrar(
            'Solicitud de recuperación de contraseña',
            'Seguridad y usuarios',
            'Se generó un enlace académico de recuperación para ' . $request->email . '.',
            $request,
            null,
            $request->email,
            'invitado'
        );

        $resetUrl = url('/password/restablecer/' . $token) . '?email=' . urlencode($request->email);

        return back()
            ->with('status', 'Se generó un enlace de recuperación.')
            ->with('reset_url', $resetUrl);
    }

    public function mostrarRestablecerPassword(Request $request, string $token)
    {
        return view('password_restablecer', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function procesarRestablecerPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $registro = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$registro || !hash_equals($registro->token, hash('sha256', $request->token))) {
            return back()->withErrors(['email' => 'El enlace de recuperación no es válido.'])->withInput();
        }

        if ($registro->created_at && now()->diffInMinutes($registro->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'El enlace de recuperación expiró. Solicita uno nuevo.'])->withInput();
        }

        $usuario = User::where('email', $request->email)->firstOrFail();
        $usuario->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        BitacoraService::registrar(
            'Cambio de contraseña exitoso',
            'Seguridad y usuarios',
            'El usuario actualizó su contraseña mediante recuperación académica.',
            $request,
            $usuario
        );

        return redirect('/login')->with('status', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
    }

    public function logout(Request $request)
    {
        BitacoraService::registrar(
            'Cierre de sesión',
            'Seguridad y usuarios',
            'El usuario cerró sesión.',
            $request
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function respuestaLoginBloqueado(Request $request, string $throttleKey)
    {
        $segundos = RateLimiter::availableIn($throttleKey);
        $minutos = max(1, (int) ceil($segundos / 60));

        return back()
            ->withInput($request->only('email'))
            ->with('login_lock_seconds', $segundos)
            ->withErrors([
                'email' => "Cuenta bloqueada temporalmente. Intente nuevamente en {$minutos} minutos.",
            ]);
    }

    public function mostrarSolicitudDocente()
    {
        return view('solicitud_docente');
    }

    public function procesarSolicitudDocente(Request $request)
    {
        $request->validate([
            'ci' => 'required|string|unique:docentes,ci',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => 'nullable|email|unique:docentes,correo',
            'telefono' => 'nullable|string|max:30',
            'profesion' => 'required|string|max:255',
        ], [
            'ci.unique' => 'Ya existe una postulación o docente registrado con esta Cédula de Identidad (CI).',
            'correo.unique' => 'Ya existe un docente registrado con este correo electrónico.',
        ]);

        Docente::create([
            'user_id' => null,
            'ci' => $request->ci,
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'profesion' => $request->profesion,
            'es_profesional' => false,
            'tiene_maestria' => false,
            'tiene_diplomado' => false,
            'estado' => 'PENDIENTE',
        ]);

        BitacoraService::registrar(
            'Solicitud de cuenta docente',
            'Seguridad y usuarios',
            'Se recibió una nueva postulación de docente: ' . $request->nombres . ' ' . $request->apellidos . ' (CI: ' . $request->ci . ').',
            $request,
            null,
            $request->correo,
            'invitado'
        );

        return back()->with('success', 'Su solicitud de cuenta docente ha sido registrada correctamente.');
    }
}
