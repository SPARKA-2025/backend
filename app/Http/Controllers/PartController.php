<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class PartController extends Controller
{
    public function fetchData($id_blok)
    {
        $data = DB::table('parts')->where('id_blok', $id_blok)->orderBy('id','asc')->get();
        if($data->isNotEmpty())
        {
            return response()->json($data);
        } else {
            return response()->json(["error" => "part untuk blok dengan id $id_blok tidak ditemukan."], 404);
        }
    }
    
    public function store(Request $request)
    {
        $blokId = $request->input('blokId');
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'blokId' => 'required|exists:bloks,id', // CEK BUG STORE
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if($blokId)
        {
            $part = new Part();
            $part->id_blok = $blokId;
            $part->nama = $request->nama;
            $part->save();
        }

        return response()->json(['message' => 'Data Created'], 200);
    }


    //CEK BUG
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|exists:parts,id',
            'nama' => 'nullable|string',
            'column' => 'nullable|integer',
            'row' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $part = DB::table('parts')->where('id', $request->id)->first();

        if ($part && $part->nama == $request->nama) {
            return response()->json(['message' => 'No changes detected'], 200);
        }

        $updated = DB::table('parts')
            ->where('id', $request->id)
            ->update(['nama' => $request->nama]);

        if ($updated) {
            return response()->json(['message' => 'Data Updated'], 200);
        }

        return response()->json(['message' => 'Data Not Found or Not Updated'], 404);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:parts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $partId = $request->id;

        DB::table('slot__parkirs')->where('id_part', $partId)->delete();
        DB::table('cctv_data')->where('id_part', $partId)->delete();
        DB::table('gateways')->where('id_part', $partId)->delete();

        $deleted = DB::table('parts')->where('id', $partId)->delete();

        if ($deleted) {
            return response()->json(['message' => 'Data Deleted Successfully'], 200);
        }

        return response()->json(['message' => 'Data Not Found'], 404);
    }

}
