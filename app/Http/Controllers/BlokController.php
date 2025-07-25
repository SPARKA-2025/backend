<?php

namespace App\Http\Controllers;

use App\Models\Blok;
use App\Models\Part;
use App\Models\Fakultas;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlokController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($fakultasId)
    {
        $fakultas = Fakultas::find($fakultasId);
        if (!$fakultas) {
            return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data = $fakultas->blok()->with('fakultas')->get();
        
        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $fakultasId)
    {
        $fakultas = Fakultas::find($fakultasId);
        if (!$fakultas) {
            return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $this->validate($request,[
            'nama' => 'required|string',
            'panjang' => 'required|string',
            'lebar' => 'required|string',
            'panjang_area' => 'required|string',
            'lebar_area' => 'required|string',
            'ukuran_box' => 'required|string',
            'deskripsi' => 'required|string'
        ]);

        $blokData = $request->only(['nama', 'id_fakultas', 'panjang', 'lebar', 'panjang_area', 'lebar_area', 'ukuran_box', 'deskripsi']);
        $blokData ['id_fakultas'] = $fakultas->id;

        try{
            $data = Blok::create($blokData);
            
            $part = new Part();
            $part->id_blok = $data->id; 
            $part->nama = 'Default Part'; 
            $part->save();

            return response()->json([
                'status' => 'success',
                'pesan' => 'Data Berhasil Ditambahkan',
                'data' => $data
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Data Gagal Ditambahkan',
                'data' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($fakultasId, $id)
    {
        $fakultas = Fakultas::find($fakultasId);
        if (!$fakultas) {
            return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data = $fakultas->blok()->with('fakultas')->find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }
        
        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Blok  $blok
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $fakultasId, $id)
    {
        $fakultas = Fakultas::find($fakultasId);
        if (!$fakultas) {
            return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data = $fakultas->blok()->find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }
        
        $data->update($request->all());
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Diupdate', 'data' => $data], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Blok  $blok
     * @return \Illuminate\Http\Response
     */
    public function destroy($fakultasId, $id)
    {
        $fakultas = Fakultas::find($fakultasId);
        if (!$fakultas) {
            return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data = $fakultas->blok()->find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }
        
        $data->delete();
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Dihapus'], Response::HTTP_OK);
    }
}
