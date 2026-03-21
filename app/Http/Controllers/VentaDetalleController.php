<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VentaDetalle;

class VentaDetalleController extends Controller
{

    public function index()
    {
        return VentaDetalle::all();
    }

    public function store(Request $request)
    {
        $detalle = VentaDetalle::create($request->all());

        return response()->json([
            "message" => "Detalle de venta creado",
            "data" => $detalle
        ],201);
    }

    public function show($id)
    {
        $detalle = VentaDetalle::findOrFail($id);

        return response()->json($detalle);
    }

    public function update(Request $request, $id)
    {
        $detalle = VentaDetalle::findOrFail($id);

        $detalle->update($request->all());

        return response()->json([
            "message" => "Detalle actualizado",
            "data" => $detalle
        ]);
    }

    public function destroy($id)
    {
        VentaDetalle::destroy($id);

        return response()->json([
            "message" => "Detalle eliminado"
        ]);
    }

}