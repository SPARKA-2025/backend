<?php

namespace App\Http\Controllers;

use App\Models\ParkirKhusus;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParkirKhususController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ParkirKhusus  $parkirKhusus
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $parkirData = ParkirKhusus::find($id);
        if (!$parkirData) {
            return response()->json(['status' => 'error', 'pesan' => 'Data tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $parkirData], Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ParkirKhusus  $parkirKhusus
     * @return \Illuminate\Http\Response
     */
    public function edit(ParkirKhusus $parkirKhusus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ParkirKhusus  $parkirKhusus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $parkirData = ParkirKhusus::find($id);
        if (!$parkirData) {
            return response()->json(['status' => 'error', 'pesan' => 'Data tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $parkirData->update($request->all());
        return response()->json(['status' => 'success', 'pesan' => 'Data berhasil diupdate', 'data' => $parkirData], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ParkirKhusus  $parkirKhusus
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $parkirData = ParkirKhusus::find($id);
        if (!$parkirData) {
            return response()->json(['status' => 'error', 'pesan' => 'Data tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $parkirData->delete();
        return response()->json(['status' => 'success', 'pesan' => 'Data berhasil dihapus'], Response::HTTP_OK);
    }
}
