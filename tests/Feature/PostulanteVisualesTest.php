<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Postulante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PostulanteVisualesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_postulante_visual_elements_are_clean(): void
    {
        // 1. Create a group and postulante with associated user
        $user = User::create([
            'name' => 'Postulante Test',
            'email' => 'postulante.test@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'postulante',
        ]);

        $grupo = Grupo::create([
            'nombre_grupo' => 'G-TEST-VISUAL',
            'turno' => 'MAÑANA',
            'cupo_maximo' => 70,
            'total_inscritos' => 1,
        ]);

        $postulante = Postulante::create([
            'user_id' => $user->id,
            'ci' => '99999999',
            'nombres' => 'Postulante',
            'apellidos' => 'Test Visual',
            'carrera_1' => 'Ingeniería de Sistemas',
            'carrera_2' => 'Ingeniería Informática',
            'colegio_procedencia' => 'Colegio Test',
            'titulo_bachiller' => false,
            'estado_pago' => false,
            'estado_inscripcion' => 'pendiente',
            'grupo_id' => $grupo->id,
        ]);

        // 2. Act as the user and visit /mi-resultado
        $response = $this->actingAs($user)->get('/mi-resultado');

        $response->assertStatus(200);
        // Assert we don't see the removed buttons in /mi-resultado
        $response->assertDontSee('Mis requisitos y pago');
        $response->assertDontSee('Actualizar envío');
        // Assert custom compact badge classes are rendered
        $response->assertSee('cup-badge-pendiente');

        // 3. Visit /mis-requisitos
        $response = $this->actingAs($user)->get('/mis-requisitos');

        $response->assertStatus(200);
        // Assert we don't see the removed header description in /mis-requisitos
        $response->assertDontSee('Marca la entrega simulada de documentos y registra tu pago digital para revisión administrativa.');
        // Assert we see "Seguimiento" instead of "Estado del envío"
        $response->assertSee('Seguimiento');
        $response->assertDontSee('Estado del envío');
        // Assert custom compact badge classes are rendered
        $response->assertSee('cup-badge-pendiente');

        // 4. Visit page and check the sidebar doesn't show "ROL ACTIVO"
        $response->assertDontSee('Rol activo');
    }
}
