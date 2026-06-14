<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cuenta Administrador
        User::updateOrCreate(
            ['email' => 'admin@ficct.uagrm.edu'],
            [
                'name' => 'Admin FICCT',
                'password' => Hash::make('password123'),
                'role' => 'admin'
            ]
        );

        // 2. Cuenta Docente
        User::updateOrCreate(
            ['email' => 'docente@ficct.uagrm.edu'],
            [
                'name' => 'Docente CUP',
                'password' => Hash::make('password123'),
                'role' => 'docente'
            ]
        );

        // 3. Cuenta Coordinador Académico
        User::updateOrCreate(
            ['email' => 'coordinador@ficct.uagrm.edu'],
            [
                'name' => 'Coordinador Académico',
                'password' => Hash::make('password123'),
                'role' => 'coordinador'
            ]
        );

        // 4. Cuenta Postulante
        User::updateOrCreate(
            ['email' => 'postulante@gmail.com'],
            [
                'name' => 'Postulante Uno',
                'password' => Hash::make('password123'),
                'role' => 'postulante'
            ]
        );
    }
}