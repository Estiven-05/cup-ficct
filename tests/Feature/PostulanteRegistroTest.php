<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Postulante;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PostulanteRegistroTest extends TestCase
{
    use DatabaseTransactions;

    public function test_inscripcion_page_loads_successfully(): void
    {
        $response = $this->get('/inscripcion');

        $response->assertStatus(200);
        $response->assertSee('Formulario de inscripción');
        $response->assertDontSee('Sistemas de Información 1');
        $response->assertDontSee('Verificación inicial');
    }

    public function test_can_register_postulante_without_checkboxes(): void
    {
        $ciTest = 'POST-' . rand(10000, 99999);
        $emailTest = 'test-postulante-' . rand(10000, 99999) . '@gmail.com';

        $response = $this->post('/registrar-postulante', [
            'ci' => $ciTest,
            'nombres' => 'Juan Pedro',
            'apellidos' => 'Pérez Soto',
            'fecha_nacimiento' => '2005-05-15',
            'sexo' => 'MASCULINO',
            'telefono' => '70011223',
            'ciudad' => 'Santa Cruz',
            'direccion' => 'Av. Bush 2do Anillo',
            'colegio_procedencia' => 'Colegio San Martín',
            'carrera_1' => 'Ingeniería de Sistemas',
            'carrera_2' => 'Ingeniería Informática',
            'correo' => $emailTest,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'usuario_login',
            'postulante',
            'grupo',
        ]);

        $this->assertDatabaseHas('postulantes', [
            'ci' => $ciTest,
            'nombres' => 'Juan Pedro',
            'apellidos' => 'Pérez Soto',
            'carrera_1' => 'Ingeniería de Sistemas',
            'titulo_bachiller' => false,
            'estado_pago' => false,
            'estado_inscripcion' => 'pendiente',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $emailTest,
            'role' => 'postulante',
        ]);
    }
}
