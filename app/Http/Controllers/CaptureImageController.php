<?php

namespace App\Http\Controllers;

use App\Models\CaptureImage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageServiceProvider;
use Intervention\Image\ImageManagerStatic as Image;

class CaptureImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $dataImage = CaptureImage::all();
        if ($dataImage -> isEmpty()){
            return response()->json(['status' => 'error', 'message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['status' => 'success', 'data' => $dataImage], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $image = $request->file('image');
        $imageData = base64_encode(file_get_contents($image));

        $imageCapture = new CaptureImage();
        $imageCapture->image = $imageData;
        $imageCapture->save();

        return response()->json(['status' => 'success', 'message' => 'Image captured successfully'], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CaptureImage  $captureImage
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $imageCapture = CaptureImage::find($id);
        $imageData = base64_decode($imageCapture->image);

        return response($imageData, Response::HTTP_OK)
                  ->header('Content-Type', 'image/jpeg'); // Sesuaikan header Content-Type sesuai dengan tipe gambar yang disimpan
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CaptureImage  $captureImage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = CaptureImage::find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data->update($request->all());
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil diupdate', 'data' => $data], Response::HTTP_OK);

        // CaptureImage::where('id',$id)->update($request->all());
        // return response()->json('Data Berhasil Diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CaptureImage  $captureImage
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = CaptureImage::find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data->delete();
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Dihapus'], Response::HTTP_OK);        

        // CaptureImage::where('id',$id)->delete();
        // return response()->json('Data Berhasil Dihapus');
    }
}
