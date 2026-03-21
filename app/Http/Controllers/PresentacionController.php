<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presentacion;

class PresentacionController extends Controller
{

public function index()
{
return Presentacion::all();
}

public function store(Request $request)
{
return Presentacion::create($request->all());
}

public function show($id)
{
return Presentacion::findOrFail($id);
}

public function update(Request $request,$id)
{
$presentacion=Presentacion::findOrFail($id);

$presentacion->update($request->all());

return $presentacion;
}

public function destroy($id)
{
Presentacion::destroy($id);

return response()->json([
"message"=>"Presentación eliminada"
]);
}

}