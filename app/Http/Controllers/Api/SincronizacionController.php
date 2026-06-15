<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistroAmbiental;
use Illuminate\Support\Facades\Http; // <-- MÁGICA LÍNEA PARA HACER PETICIONES HTTP
use Illuminate\Support\Facades\DB;

class SincronizacionController extends Controller
{
    public function sincronizar(Request $request)
    {
        $userId = $request->input('user_id');
        $registros = $request->input('registros', []);
        $procesados = 0;

        DB::beginTransaction();
        try {
            foreach ($registros as $data) {

                // Variable por defecto por si falla la red o no hay coordenadas
                $municipioDetectado = 'No registrado';

                // SI EL REGISTRO TIENE COORDENADAS, HACEMOS GEOCODIFICACIÓN INVERSA
                if (!empty($data['latitud']) && !empty($data['longitud'])) {
                    try {
                        // Consultamos de forma segura a OpenStreetMap
                        // Nota: Nominatim exige un 'User-Agent' identificable para no bloquear la petición
                        $response = Http::withHeaders([
                            'User-Agent' => 'EcoBitacoraCIIDIR/1.0 (andro_97@hotmail.com)'
                        ])->get('https://nominatim.openstreetmap.org/reverse', [
                            'lat'    => $data['latitud'],
                            'lon'    => $data['longitud'],
                            'format' => 'json',
                            'addressdetails' => 1
                        ]);

                        if ($response->successful()) {
                            $resultado = $response->json();
                            $address = $resultado['address'] ?? [];

                            // Nominatim en México suele guardar el municipio en la clave 'county' o 'municipality'
                            $municipioDetectado = $address['county'] ?? $address['municipality'] ?? $address['city'] ?? $address['town'] ?? 'Por geolocalizar';

                            // Limpieza estética: "Municipio de Santa Cruz Xoxocotlán" -> "Santa Cruz Xoxocotlán"
                            $municipioDetectado = str_replace('Municipio de ', '', $municipioDetectado);
                        }
                    } catch (\Exception $apiError) {
                        // Si el servidor de mapas se cae, el sistema no se detiene, continúa con 'Por geolocalizar'
                        $municipioDetectado = 'Error de red en mapa';
                    }
                }

                // GUARDAMOS O ACTUALIZAMOS EL REGISTRO EN TiDB CLOUD ENRIQUECIDO
                RegistroAmbiental::updateOrCreate(
                    ['uuid' => $data['uuid']], // Llave estricta de búsqueda (Idempotencia)
                    [
                        'user_id'       => $userId,
                        'fecha'         => $data['fecha'],
                        'timestamp'     => $data['timestamp'],
                        'eje'           => $data['eje'],
                        'categoria'     => $data['categoria'],
                        'subcategoria'  => $data['subcategoria'],
                        'cantidad'      => $data['cantidad'],
                        'observaciones' => $data['observaciones'] ?? null,
                        'latitud'       => $data['latitud'],
                        'longitud'      => $data['longitud'],
                        'municipio'     => $municipioDetectado, // <-- ¡AQUÍ ENTRA EL MUNICIPIO REAL TRADUCIDO!
                        'sincronizado'  => 1
                    ]
                );

                $procesados++;
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Sincronización exitosa. {$procesados} registros procesados."
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Falla crítica en el lote: ' . $e->getMessage()
            ], 500);
        }
    }
}
