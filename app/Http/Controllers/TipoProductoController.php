<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoProducto;

class TipoProductoController extends Controller
{

public function index()
{
return TipoProducto::all();
}

public function store(Request $request)
{
return TipoProducto::create($request->all());
}

public function show($id)
{
return TipoProducto::findOrFail($id);
}

public function update(Request $request,$id)
{
$tipo=TipoProducto::findOrFail($id);

$tipo->update($request->all());

return $tipo;
}

public function destroy($id)
{
TipoProducto::destroy($id);

return response()->json([
"message"=>"Tipo de producto eliminado"
]);
}

}