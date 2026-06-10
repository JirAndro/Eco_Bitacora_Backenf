<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistroAmbiental;
use Illuminate\Support\Facades\DB;

class SincronizacionController extends Controller
{
    public function sincronizar(Request $request)
    {
        // Validamos que nos envíen un arreglo de registros y un ID de usuario
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'registros' => 'required|array',
        ]);

        $userId = $request->user_id;
        $registros = $request->registros;
        $guardados = 0;

        DB::beginTransaction();
        try {
            foreach ($registros as $item) {
                // updateOrCreate busca por UUID. Si existe, lo actualiza. Si no, lo crea.
                RegistroAmbiental::updateOrCreate(
                    ['uuid' => $item['uuid']],
                    [
                        'user_id' => $userId,
                        'fecha' => $item['fecha'],
                        'timestamp' => $item['timestamp'],
                        'eje' => $item['eje'],
                        'categoria' => $item['categoria'],
                        'subcategoria' => $item['subcategoria'] ?? null,
                        'cantidad' => $item['cantidad'],
                        'observaciones' => $item['observaciones'] ?? null,
                        'latitud' => $item['latitud'] ?? null,
                        'longitud' => $item['longitud'] ?? null,
                        // El manejo de la foto física se hace en otra ruta, aquí solo guardamos null o un string temporal
                        'fotoPath' => null,
                    ]
                );
                $guardados++;
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Sincronización exitosa. $guardados registros procesados.",
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al sincronizar en la base de datos central.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
