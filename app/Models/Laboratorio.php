<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    protected $table = 'laboratorios';

    protected $primaryKey = 'id_laboratorio';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nombre',
        'creado_en'
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_laboratorio');
    }
}