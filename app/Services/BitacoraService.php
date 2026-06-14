<?php

namespace App\Services;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class BitacoraService
{
    public static function registrar(
        string $accion,
        string $modulo,
        string $descripcion,
        ?Request $request = null,
        ?User $user = null,
        ?string $nombreUsuario = null,
        ?string $rol = null
    ): void {
        try {
            $usuario = $user ?: Auth::user();

            Bitacora::create([
                'user_id' => optional($usuario)->id,
                'nombre_usuario' => $nombreUsuario ?: optional($usuario)->name ?: 'No autenticado',
                'rol' => $rol ?: optional($usuario)->role ?: 'invitado',
                'accion' => $accion,
                'modulo' => $modulo,
                'descripcion' => $descripcion,
                'ip' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
