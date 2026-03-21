<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presentacion extends Model
{
    protected $table = 'presentacions';

    protected $primaryKey = 'id_presentacion';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nombre'
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_presentacion');
    }
}