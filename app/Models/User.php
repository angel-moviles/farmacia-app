<?php


namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable

{

    use HasApiTokens, Notifiable;


    protected $table = 'usuarios'; // Tabla de la UTVT

    protected $primaryKey = 'id_usuario';


    // Laravel busca 'password', tú usas 'contrasena'

    public function getAuthPassword()

    {

        return $this->contrasena;

    }


    protected $fillable = [

        'clave_usuario', 'nombre', 'a_paterno', 'a_materno', 

        'fecha_nacimiento', 'sexo', 'telefono', 'correo', 

        'contrasena', 'id_rol', 'activo'

    ];


    protected $hidden = ['contrasena', 'remember_token'];

}
