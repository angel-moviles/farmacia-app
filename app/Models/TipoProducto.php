<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoProducto extends Model
{
    protected $table = 'tipo_productos';

    protected $primaryKey = 'id_tipo_producto';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nombre'
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_tipo_producto');
    }
}