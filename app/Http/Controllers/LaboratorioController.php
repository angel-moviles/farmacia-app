<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laboratorio;

class LaboratorioController extends Controller
{

public function index()
{
return Laboratorio::all();
}

public function store(Request $request)
{
return Laboratorio::create($request->all());
}

public function show($id)
{
return Laboratorio::findOrFail($id);
}

public function update(Request $request,$id)
{
$laboratorio=Laboratorio::findOrFail($id);
$laboratorio->update($request->all());

return $laboratorio;
}

public function destroy($id)
{
Laboratorio::destroy($id);

return response()->json([
"message"=>"Laboratorio eliminado"
]);
}

}