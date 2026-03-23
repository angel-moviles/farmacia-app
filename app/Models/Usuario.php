<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    // Activamos timestamps ya que tu migración los tiene
    public $timestamps = true;

    protected $fillable = [
        'clave_usuario',
        'nombre',
        'a_paterno',
        'a_materno',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'correo',
        'foto',
        'contrasena',
        'activo',
        'id_rol'
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_nacimiento' => 'date',
    ];

    // Esto hace que 'foto_url' aparezca siempre en el JSON de respuesta
    protected $appends = ['foto_url'];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    /**
     * Genera la URL completa para la imagen.
     * Requiere haber ejecutado: php artisan storage:link
     */
    public function getFotoUrlAttribute()
        {
            if ($this->foto) {
                // Verificar si el archivo existe
                $storagePath = storage_path('app/public/usuarios/' . $this->foto);
                if (file_exists($storagePath)) {
                    return url('/api/images/usuarios/' . $this->foto);
                }
            }
            return null;
        }
}