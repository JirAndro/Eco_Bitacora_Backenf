<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroAmbiental extends Model
{
    use HasFactory;

    protected $table = 'registros_ambientales';

    protected $fillable = [
        'uuid',
        'user_id',
        'fecha',
        'timestamp',
        'eje',
        'categoria',
        'subcategoria',
        'cantidad',
        'observaciones',
        'latitud',
        'longitud',
        'fotoPath',
        'municipio'
    ];

    // Relación: Un registro pertenece a un usuario (investigador)
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
}
