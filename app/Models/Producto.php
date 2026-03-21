<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps = true;

    protected $fillable = [
        'lote',
        'codigo_barras',
        'nombre',
        'foto',  // Agregar este campo
        'fecha_produccion',
        'fecha_caducidad',
        'costo',
        'precio_venta',
        'stock',
        'stock_minimo',
        'activo',
        'id_laboratorio',
        'id_tipo_producto',
        'id_presentacion',
        'id_proveedor'
    ];

    protected $casts = [
        'fecha_produccion' => 'date',
        'fecha_caducidad' => 'date',
        'costo' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'activo' => 'boolean'
    ];

    protected $appends = ['foto_url'];

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class, 'id_laboratorio');
    }

    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class, 'id_tipo_producto');
    }

    public function presentacion()
    {
        return $this->belongsTo(Presentacion::class, 'id_presentacion');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class, 'id_producto');
    }

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return url('storage/productos/' . $this->foto);
        }
        return null;
    }
}