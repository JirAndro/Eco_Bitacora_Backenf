<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    // Usamos RefreshDatabase si queremos que Laravel limpie
    // una base de datos de prueba automáticamente, pero por ahora
    // solo haremos pruebas HTTP directas.

    /**
     * Prueba 1: Verificar que el panel de investigadores responda.
     */
    public function test_el_dashboard_carga_correctamente()
    {
        // Simulamos entrar a la URL en el navegador
        $response = $this->get('/dashboard');



        // Afirmamos que el servidor responde con 200 OK
        $response->assertStatus(200);

        // Afirmamos que la vista contiene el título esperado
        $response->assertSee('Dashboard: Eco-Bitácora');
    }

    /**
     * Prueba 2: Verificar que el motor de exportación genera el CSV.
     */
    public function test_la_exportacion_descarga_un_archivo_csv()
    {
        // Simulamos hacer clic en el botón de descarga
        $response = $this->get('/dashboard/exportar');

        // Afirmamos que no hay error de servidor
        $response->assertStatus(200);

        // Afirmamos que las cabeceras HTTP son exactamente de un archivo CSV
        $response->assertHeader('Content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }
}
