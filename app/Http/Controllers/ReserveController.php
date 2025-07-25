<?php

namespace App\Http\Controllers;

use App\Models\Reserve;
use Illuminate\Http\Request;
// use \Illuminate\Http\Response;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class ReserveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $reserves = Reserve::with('parkir.slot__parkir.blok.fakultas')->get();
        if (!$reserves) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }
        // Mengembalikan respons HTTP dengan data yang diperoleh dari query
        return response()->json(['status' => 'success', 'data' => $reserves], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $this->validate($request,[
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'required|date',
            'id_parkir' => 'required|integer|exists:parkirs,id',
            'id_user' => 'required|integer|exists:users,id'
        ]);

        $reservedData = $request->only(['tanggal_masuk', 'tanggal_keluar', 'id_parkir', 'id_user']);

        try{
            $reserve = Reserve::create($reservedData);
            return response()->json([
                'status' => 'success',
                'pesan' => 'Data Berhasil Ditambahkan',
                'data' => $reserve
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Data Gagal Ditambahkan',
                'data' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        // $reserve =[
        //     'tanggal_masuk' => $request->input('tanggal_masuk'),
        //     'tanggal_keluar' => $request->input('tanggal_keluar'),
        //     'id_parkir' => $request->input('id_parkir'),
        //     'id_user' => $request->input('id_user')
        // ];
        // $data = Reserve::create($reserve);
        // if ($data) {
        //     $result = [
        //         'pesan' => 'Data Berhasil Ditambahkan',
        //         'data' => $reserve
        //     ];
        // }
        // else{
        //     $result = [
        //         'pesan' => 'Data Gagal Ditambahkan',
        //         'data' => ""
        //     ];
        // }
        // return response()->json($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $data = Reserve::with('parkir.slot__parkir.blok.fakultas')->find($id);

        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reserve  $reserve
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $reserve = Reserve::find($id);
        if (!$reserve) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $reserve->update($request->all());
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Diupdate', 'data' => $reserve], Response::HTTP_OK);

        // $data = Reserve::findOrFail($id);
        // $data->update($request->all());
        // Reserve::where('id',$id)->update($request->all());
        // return response()->json("Data Sudah diupdate");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reserve  $reserve
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $reserve = Reserve::find($id);
        if (!$reserve) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $reserve->delete();
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Dihapus'], Response::HTTP_OK);

        // Reserve::where('id',$id)->delete();
        // return response()->json("Data Sudah Dihapus");
    }

    public function downloadStrukReservasi($id)
    {
        // Cari data reservasi berdasarkan $id
        $reservasi = Reserve::findOrFail($id);

        // Buat objek Dompdf
        $dompdf = new Dompdf();

        // Atur opsi jika diperlukan (misalnya, ukuran dan orientasi halaman)
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf->setOptions($options);

        // Buat isi dokumen HTML untuk struk reservasi
        $html = view('pdf.struk_reservasi', compact('reserve'))->render();

        // Muat isi dokumen HTML ke Dompdf
        $dompdf->loadHtml($html);

        // Render PDF
        $dompdf->render();

        // Atur nama file PDF yang akan diunduh
        $fileName = 'struk_reservasi_' . $reservasi->id . '.pdf';

        // Unduh file PDF
        return $dompdf->stream($fileName);
    }
}
