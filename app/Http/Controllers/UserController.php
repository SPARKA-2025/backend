<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Token;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $data = User::all();
        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Data Not Found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $nama = $request->nama;
        $email = $request->email;
        $password = $request->password;
        //  $alamat = $request->alamat; bug delete alamat on register
        $phone = $request->phone;
        $plat_nomor = $request->plat_nomor;

        // Check if field is empty
        if (empty($nama) or empty($email) or empty($password) or empty($phone)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields'], Response::HTTP_BAD_REQUEST);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 'error', 'message' => 'You must enter a valid email'], Response::HTTP_BAD_REQUEST);
        }

        // Check email domain requirements
        $allowedDomains = ['gmail.com', 'mail.unnes.ac.id'];
        $emailDomain = substr(strrchr($email, "@"), 1);
        if (!in_array($emailDomain, $allowedDomains)) {
            return response()->json(['status' => 'error', 'message' => 'Email domain is not allowed'], Response::HTTP_BAD_REQUEST);
        }

        // Check if password is greater than 5 character
        if (strlen($password) < 8) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character'], Response::HTTP_BAD_REQUEST);
        }

        // Check if user already exist
        if (User::where('email', '=', $email)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'User already exists with this email'], Response::HTTP_BAD_REQUEST);
        }

        // Check if phone is valid
        if (!is_numeric($phone) || strlen($phone) > 12) {
            return response()->json(['status' => 'error', 'message' => 'Phone number must be numeric and no more than 12 digits'], Response::HTTP_BAD_REQUEST);
        }

        // Check if phone already exist
        if (User::where('phone', '=', $phone)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Phone number already exist'], Response::HTTP_BAD_REQUEST);
        }

        // Create new user
        try {
            $user = new User();
            $user->nama = $request->nama;
            $user->email = $request->email;
            $user->password = app('hash')->make($request->password);
            // $user->alamat = $request->alamat;
            $user->phone = $request->phone;
            $user->plat_nomor = $request->plat_nomor;

            if ($user->save()) {
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        // Check if field is empty
        if (empty($email) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields'], Response::HTTP_BAD_REQUEST);
        }

        $credentials = ['email' => $email, 'password' => $password];

        // Mengecek apakah email yang dimasukkan sudah benar atau belum
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Email is not registered'], Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Password is incorrect'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // Set token expiration to 1 hour
        $expiredAt = Carbon::now()->addHour();

        // Check if token already exists for this user
        $existingToken = Token::where('id_user', $user->id)->first();

        if ($existingToken) {
            $existingToken->api_token = $token;
            $existingToken->expired_at = $expiredAt;
            $existingToken->save();
        } else {
            Token::create([
                'id_user' => $user->id,
                'api_token' => $token,
                'expired_at' => $expiredAt,
            ]);
        }

        return $this->respondWithTokenUser($token);
    }

    protected function respondWithTokenUser($token)
    {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
    }

    public function create(Request $request)
    {
        //
        $this->validate($request,[
            'nama' => 'required|string',
            'email' => 'required|string',
            'password' => 'required|string|min:8',
            //'alamat' => 'required|string',
            'phone' => 'required|string',
            // 'id_slot' => 'required|integer|exists:slot__parkirs,id'
        ]);

        $userData = $request->only('nama', 'email', 'password', 'phone');
        try{
            $user = User::create($userData);
            return response()->json([
                'status' => 'success',
                'pesan' => 'Data Berhasil Ditambahkan',
                'data' => $user
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
    public function show($id)
    {
        //
        // $data = User::where('id',$id)->get();
        // return response()->json($data);
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User Not Found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['status' => 'success', 'data' => $user], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function updateImage(Request $request, $id)
    {
        // Cari user berdasarkan ID
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Validasi untuk file gambar
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1048',
        ]);

        try {
            // Proses file gambar
            $image = $request->file('image');
            $imageData = base64_encode(file_get_contents($image)); // Menyimpan dalam bentuk Base64 (opsional)

            // Menyimpan gambar dalam kolom 'image' di database
            $user->image = $imageData;

            // Simpan data user
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile image updated successfully',
                'data' => $user,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile image',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateData(Request $request, $id) {

        // Cari user id
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'pesan' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $this->validate($request, [
            'nama' => 'string',
            'email' => 'string',
            'password' => 'string|min:8',
            'alamat' => 'string',
            'phone' => 'string',
            'plat_nomor' => 'string|nullable'
        ]);

        $userData = $request->only(['nama', 'email','alamat', 'phone', 'plat_nomor']);

        if ($request->filled('password')) {
            $userData['password'] = app('hash')->make($request->password);
        }

        $user->update($userData);

        $response = [
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'password' => $user->password,
                'alamat' => $user->alamat,
                'phone' => $user->phone,
                'plat_nomor' => $user->plat_nomor
            ]
        ];
        return response()->json(['status' => 'success', 'pesan' => 'Data berhasil diupdate', 'data' => $response], Response::HTTP_OK);
    }


    /**
     * Update plat nomor user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePlatNomor(Request $request, $id) {
        // Cari user berdasarkan ID
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'pesan' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Ambil plat nomor dari request
        $platNomor = $request->input('plat_nomor');
        
        // Validasi manual untuk form-data compatibility
        if (empty($platNomor)) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Plat nomor harus diisi',
                'plat_nomor' => ['The plat nomor field is required.']
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        if (strlen($platNomor) > 10) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Plat nomor tidak boleh lebih dari 10 karakter',
                'plat_nomor' => ['The plat nomor may not be greater than 10 characters.']
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            // Update plat nomor
            $user->plat_nomor = $platNomor;
            $user->save();

            return response()->json([
                'status' => 'success',
                'pesan' => 'Plat nomor berhasil diupdate',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'plat_nomor' => $user->plat_nomor
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal mengupdate plat nomor',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // logout
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User Not Found'], Response::HTTP_NOT_FOUND);
        }
        $user->delete();
        return response()->json(['status' => 'success', 'data' => $user], Response::HTTP_OK);
    }

    public function profile()
    {
        $user = auth()->user();
        
        // Filter hanya field yang diinginkan
        $profileData = [
            'nama' => $user->nama,
            'email' => $user->email,
            'alamat' => $user->alamat,
            'phone' => $user->phone,
            'image' => $user->image,
            'plat_nomor' => $user->plat_nomor
        ];
        
        // Jika image berupa base64, tambahkan prefix
        if ($user->image && !filter_var($user->image, FILTER_VALIDATE_URL)) {
            $profileData['image'] = 'data:image/jpeg;base64,' . $user->image;
        }

        return response()->json([
            'status' => 'success',
            'data' => $profileData
        ]);
    }

}
