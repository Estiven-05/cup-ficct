<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Docente;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DocenteSolicitudTest extends TestCase
{
    use DatabaseTransactions;

    public function test_solicitud_docente_page_loads_successfully(): void
    {
        $response = $this->get('/solicitud-docente');

        $response->assertStatus(200);
        $response->assertSee('Solicitud de Cuenta Docente');
        $response->assertSee('Cédula de Identidad (CI)');
    }

    public function test_can_submit_docente_solicitud_successfully(): void
    {
        $response = $this->post('/solicitud-docente', [
            'ci' => '12345678',
            'nombres' => 'Carlos',
            'apellidos' => 'Justiniano Méndez',
            'correo' => 'carlos@uagrm.edu.bo',
            'telefono' => '78901234',
            'profesion' => 'Lic. en Ciencias de la Computación',
        ]);

        $response->assertStatus(302); // Redirect back
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('docentes', [
            'ci' => '12345678',
            'nombres' => 'Carlos',
            'apellidos' => 'Justiniano Méndez',
            'correo' => 'carlos@uagrm.edu.bo',
            'telefono' => '78901234',
            'profesion' => 'Lic. en Ciencias de la Computación',
            'estado' => 'PENDIENTE',
            'user_id' => null,
        ]);
    }

    public function test_solicitud_docente_requires_fields(): void
    {
        $response = $this->post('/solicitud-docente', [
            'ci' => '',
            'nombres' => '',
            'apellidos' => '',
            'profesion' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['ci', 'nombres', 'apellidos', 'profesion']);
        $this->assertDatabaseCount('docentes', 0);
    }
}
