<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RegistroAmbiental;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra el panel principal con los registros y sus recolectores.
     */
public function index()
{
    // 1. Traemos todos los registros
    $registros = RegistroAmbiental::with('user')->orderBy('timestamp', 'desc')->get();

    // 2. Lógica para la gráfica (Dona)
    $datosGrafica = RegistroAmbiental::selectRaw('eje, count(*) as total')
                        ->groupBy('eje')
                        ->pluck('total', 'eje');
    $labelsEjes = $datosGrafica->keys();
    $valoresEjes = $datosGrafica->values();

    // 3. NUEVO: Cálculos para las tarjetas (KPIs)
    // Sumamos la columna 'cantidad' filtrando por el nombre del Eje
    $volumenAgua = RegistroAmbiental::where('eje', 'Agua')->sum('cantidad');
    $masaResiduos = RegistroAmbiental::where('eje', 'Residuos')->sum('cantidad');

    // 4. Pasamos TODAS las variables a la vista
    return view('dashboard.index', compact(
        'registros',
        'labelsEjes',
        'valoresEjes',
        'volumenAgua',
        'masaResiduos'
    ));
}

    /**
     * Exporta la base de datos completa a un archivo estructurado CSV legible por Excel.
     */
    public function exportar()
    {
        // Traemos los registros junto con los nombres de los usuarios de campo
        $registros = RegistroAmbiental::with('user')->get();
        $nombreArchivo = "bitacoras_ciidir_" . date('Y-m-d') . ".csv";
        $cabeceras = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$nombreArchivo",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        // Columnas actualizadas para el reporte de los investigadores
        $columnas = ['ID', 'Usuario de Campo', 'Eje', 'Categoría', 'Subcategoría', 'Cantidad', 'Municipio', 'Fecha de Registro'];
        $callback = function() use($registros, $columnas) {
            $archivo = fopen('php://output', 'w');
            // Forzar codificación UTF-8 para compatibilidad con acentos en Excel
            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($archivo, $columnas);
            foreach ($registros as $fila) {
                fputcsv($archivo, [
                    $fila->id,
                    $fila->user->name ?? 'No asignado',
                    $fila->eje,
                    $fila->categoria,
                    $fila->subcategoria ?? 'N/A',
                    $fila->cantidad,
                    $fila->municipio ?? 'Por geolocalizar',
                    $fila->timestamp
                ]);
            }
            fclose($archivo);
        };
        return response()->stream($callback, 200, $cabeceras);
    }
}
