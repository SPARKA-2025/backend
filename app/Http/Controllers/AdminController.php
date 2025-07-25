<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\TokenAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    public function adminRegister(Request $request)
    {
        $nama = $request->nama;
        $email = $request->email;
        $password = $request->password;

        // Check if field is empty
        if (empty($nama) or empty($email) or empty($password)) {
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
            return response()->json(['status' => 'error', 'message' => 'Password should be min 8 character'], Response::HTTP_BAD_REQUEST);
        }

        // Check if user already exist
        if (Admin::where('email', '=', $email)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'User already exists with this email'], Response::HTTP_BAD_REQUEST);
        }

        // Create new user
        try {
            $admin = new Admin();
            $admin->nama = $request->nama;
            $admin->email = $request->email;
            $admin->password = app('hash')->make($request->password);

            if ($admin->save()) {
                return $this->loginAdmin($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // public function loginAdmin(Request $request)
    // {
    //     $email = $request->email;
    //     $password = $request->password;

    //     // Check if fields are empty
    //     if (empty($email) or empty($password)) {
    //         return response()->json(['status' => 'error', 'message' => 'You must fill all the fields'], Response::HTTP_BAD_REQUEST);
    //     }

    //     // Check if the admin exists with the provided email
    //     $admin = Admin::where('email', $email)->first();
    //     if (!$admin) {
    //         return response()->json(['status' => 'error', 'message' => 'Email is not registered'], Response::HTTP_UNAUTHORIZED);
    //     }

    //     // Verify the password
    //     if (!Hash::check($password, $admin->password)) {
    //         return response()->json(['status' => 'error', 'message' => 'Password is incorrect'], Response::HTTP_UNAUTHORIZED);
    //     }

    //     $credentials = request(['email', 'password']);

    //     // JWTAuth::factory()->setTTL(60);
    //     JWTAuth::factory()->setTTL(5256000);

    //     // Attempt to generate token
    //     if (!$token = Auth::guard('admin')->attempt($credentials)) {
    //         return response()->json(['status' => 'error', 'message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    //     }

    //     $admin = Auth::guard('admin')->user();

    //     // $expiredAt = Carbon::now()->addMinutes(JWTAuth::factory()->getTTL());
    //     $expiredAt = Carbon::now()->addYears(10); // Set expiration to 10 years

    //     $existingToken = TokenAdmin::where('id_admin', $admin->id)->first();

    //     // Token disimpan di database
    //     if ($existingToken) {
    //         $existingToken->api_token = $token;
    //         $existingToken->expired_at = $expiredAt;
    //         $existingToken->save();
    //     } else {
    //         TokenAdmin::create([
    //             'id_admin' => $admin->id,
    //             'api_token' => $token,
    //             'expired_at' => $expiredAt
    //         ]);
    //     }

    //     return $this->respondWithTokenAdmin($token);
    // }

    // public function logoutAdmin()
    // {
    //     auth()->logout();

    //     return response()->json(['status' => 'success', 'message' => 'Successfully logged out'], Response::HTTP_OK);
    // }

    // protected function respondWithTokenAdmin($token)
    // {
    //     return response()->json([
    //         'access_token' => $token,
    //         'token_type' => 'bearer',
    //         // 'expires_in' => JWTAuth::factory()->getTTL() * 60
    //         'expires_in' => 5256000 * 60 // 10 years in seconds
    //     ]);
    // }

    public function loginAdmin(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        // Check if fields are empty
        if (empty($email) || empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields'], Response::HTTP_BAD_REQUEST);
        }

        // Check if the admin exists with the provided email
        $admin = Admin::where('email', $email)->first();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'Email is not registered'], Response::HTTP_UNAUTHORIZED);
        }

        // Verify the password
        if (!Hash::check($password, $admin->password)) {
            return response()->json(['status' => 'error', 'message' => 'Password is incorrect'], Response::HTTP_UNAUTHORIZED);
        }

        // menambahkan atribut nama di token jwt
        $customClaims = ['id' => $admin->id, 'nama' => $admin->nama];

        // Set TTL to 10 years
        JWTAuth::factory()->setTTL(5256000); // 10 years in minutes

        // Attempt to generate token
        if (!$token = Auth::guard('admin')->claims($customClaims)->attempt($request->only('email', 'password'))) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $admin = Auth::guard('admin')->user();

        // Set expiration to 10 years
        $expiredAt = Carbon::now()->addYears(10);

        $existingToken = TokenAdmin::where('id_admin', $admin->id)->first();

        // Token disimpan di database
        if ($existingToken) {
            $existingToken->api_token = $token;
            $existingToken->expired_at = $expiredAt;
            $existingToken->save();
        } else {
            TokenAdmin::create([
                'id_admin' => $admin->id,
                'api_token' => $token,
                'expired_at' => $expiredAt
            ]);
        }
        return $this->respondWithTokenAdmin($token);
        
    }

    public function index()
    {
        $data = Admin::all();
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    public function show($id)
    {
        $data = Admin::find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    public function update(Request $request, $id) {

        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['status' => 'error', 'pesan' => 'Admin not found'], Response::HTTP_NOT_FOUND);
        }

        $this->validate($request, [
            'nama' => 'sometimes|string',
            'email' => 'sometimes|string',
            'password' => 'sometimes|string|min:8',
        ]);

        $adminData = $request->only(['nama', 'email']);

        if ($request->filled('password')) {
            $adminData['password'] = app('hash')->make($request->password);
        }

        $admin->update($adminData);

        $response = [
            'status' => 'success',
            'data' => [
                'id' => $admin->id,
                'nama' => $admin->nama,
                'email' => $admin->email,
                'password' => $admin->password
            ]
        ];
        return response()->json(['status' => 'success', 'pesan' => 'Data berhasil diupdate', 'data' => $response], Response::HTTP_OK);
    }

    public function destroy(Request $request, $id) {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['status' => 'error', 'pesan' => 'Admin not found'], Response::HTTP_NOT_FOUND);
        }

        $admin->delete();
        return response()->json(['status' => 'success', 'pesan' => 'Data berhasil dihapus'], Response::HTTP_OK);
    }

    public function getToken(Request $request)
    {
        try {
            // Get admin ID from JWT token in Authorization header
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'No token provided'], Response::HTTP_UNAUTHORIZED);
            }

            // Decode the token to get admin info
            $payload = JWTAuth::setToken($token)->getPayload();
            $adminId = $payload->get('id');

            if (!$adminId) {
                return response()->json(['status' => 'error', 'message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
            }

            // Get token from database
            $tokenAdmin = TokenAdmin::where('id_admin', $adminId)->first();
            
            if (!$tokenAdmin) {
                return response()->json(['status' => 'error', 'message' => 'Token not found'], Response::HTTP_NOT_FOUND);
            }

            // Check if token is expired
            if (Carbon::now()->gt($tokenAdmin->expired_at)) {
                return response()->json(['status' => 'error', 'message' => 'Token expired'], Response::HTTP_UNAUTHORIZED);
            }

            return response()->json([
                'status' => 'success',
                'token' => $tokenAdmin->api_token,
                'expires_at' => $tokenAdmin->expired_at
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to get token: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logoutAdmin()
    {
        auth()->logout();

        return response()->json(['status' => 'success', 'message' => 'Successfully logged out'], Response::HTTP_OK);

        try {
            // Invalidating the token, logging out the user
            JWTAuth::invalidate(JWTAuth::getToken());
    
            return response()->json(['status' => 'success', 'message' => 'Successfully logged out'], Response::HTTP_OK);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to log out, invalid token'], Response::HTTP_UNAUTHORIZED);
        }
    }

    protected function respondWithTokenAdmin($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 5256000 * 60 // 10 years in seconds
        ]);
    }

/////////////////////////////////////////////////////////////////////////////////////////

//     /**
//      * Display a listing of the resource.
//      *
//      * @return \Illuminate\Http\Response
//      */
//     public function UserIndex()
//     {
//         //
//         $data = User::all();
//         if (!$data) {
//             return response()->json(['status' => Response::HTTP_NOT_FOUND, 'message' => 'Data Not Found']);
//         }
//         return response()->json(['status' => Response::HTTP_OK, 'data' => $data]);
//     }

//     /**
//      * Show the form for creating a new resource.
//      *
//      * @return \Illuminate\Http\Response
//      */
//     public function UserRegister(Request $request)
//     {
//         $nama = $request->nama;
//         $email = $request->email;
//         $password = $request->password;
//         $alamat = $request->alamat;
//         $phone = $request->phone;

//         // Check if field is empty
//         if (empty($nama) or empty($email) or empty($password) or empty($alamat) or empty($phone)) {
//             return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
//         }

//         // Check if email is valid
//         if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//             return response()->json(['status' => 'error', 'message' => 'You must enter a valid email']);
//         }

//         // Check if password is greater than 5 character
//         if (strlen($password) < 8) {
//             return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
//         }

//         // Check if user already exist
//         if (User::where('email', '=', $email)->exists()) {
//             return response()->json(['status' => 'error', 'message' => 'User already exists with this email']);
//         }

//         // Check if phone is valid
//         if (!is_numeric($phone) || strlen($phone) > 12) {
//             return response()->json(['status' => 'error', 'message' => 'Phone number must be numeric and no more than 12']);
//         }

//         // Check if phone already exists
//         if (User::where('phone', '=', $phone)->exists()) {
//             return response()->json(['status' => 'error', 'message' => 'Phone number already exists']);
//         }

//         // Create new user
//         try {
//             $user = new User();
//             $user->nama = $request->nama;
//             $user->email = $request->email;
//             $user->password = app('hash')->make($request->password);
//             $user->alamat = $request->alamat;
//             $user->phone = $request->phone;

//             if ($user->save()) {
//                 return $this->UserLogin($request);
//             }
//         } catch (\Exception $e) {
//             return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
//         }
//     }

//     public function UserLogin(Request $request)
//     {
//         $email = $request->email;
//         $password = $request->password;

//         // Check if field is empty
//         if (empty($email) or empty($password)) {
//             return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
//         }

//         $credentials = request(['email', 'password']);

//         if (!$token = auth()->attempt($credentials)) {
//             return response()->json(['error' => 'Unauthorized'], 401);
//         }

//         $user = auth()->user();

//         // Check if token already exists for this user
//         $existingToken = Token::where('id_user', $user->id)->first();

//         $expiredAt = Carbon::now()->addHour();

//         if ($existingToken) {
//             // Update the existing token
//             $existingToken->api_token = $token;
//             $existingToken->expired_at = $expiredAt;
//             $existingToken->save();
//         } else {
//             // Create new token
//             Token::create([
//                 'id_user' => $user->id,
//                 'api_token' => $token,
//                 'expired_at' => $expiredAt
//         ]);
//     }
//         return $this->respondWithTokenUser($token);
//     }

//     protected function respondWithTokenUser($token)
//     {
//             return response()->json([
//                 'access_token' => $token,
//                 'token_type' => 'bearer',
//                 'expires_in' => auth()->factory()->getTTL() * 60
//             ]);
//     }

//     /**
//      * Display the specified resource.
//      *
//      * @param  int  $id
//      * @return \Illuminate\Http\Response
//      */
//     public function UserShow($id)
//     {
//         //
//         // $data = User::where('id',$id)->get();
//         // return response()->json($data);
//         $user = User::find($id);
//         if (!$user) {
//             return response()->json(['status' => Response::HTTP_NOT_FOUND, 'message' => 'User Not Found']);
//         }
//         return response()->json(['status' => Response::HTTP_OK, 'data' => $user]);

//         // if ($user) {
//         //     return response()->json([
//         //         'success' => true,
//         //         'message' => 'User Found',
//         //         'data' => $user
//         //     ], 200);
//         // }
//         // else {
//         //     return response()->json([
//         //         'success' => false,
//         //         'message' => 'User Not Found',
//         //         'data' => ''
//         //     ], 404);
//         // }
//     }

//     /**
//      * Update the specified resource in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \App\Models\User  $user
//      * @return \Illuminate\Http\Response
//      */
//     public function UserUpdate(Request $request, $id)
//     {
//         $user = User::find($id);
//         if (!$user) {
//             return response()->json(['status' => Response::HTTP_NOT_FOUND, 'message' => 'User Not Found']);
//         }
//         $user->update($request->all());
//         return response()->json(['status' => Response::HTTP_OK, 'data' => $user]);

//         // User::where('id',$id)->update($request->all());
//         // return response()->json("Data Sudah diupdate");
//     }

//     /**
//      * Remove the specified resource from storage.
//      *
//      * @param  int  $id
//      * @return \Illuminate\Http\Response
//      */
//     public function UserDestroy($id)
//     {
//         $user = User::find($id);
//         if (!$user) {
//             return response()->json(['status' => Response::HTTP_NOT_FOUND, 'message' => 'User Not Found']);
//         }
//         $user->delete();
//         return response()->json(['status' => Response::HTTP_OK, 'data' => $user]);

//         // User::where('id',$id)->delete();
//         // return response()->json("Data Sudah Dihapus");
//     }

// /////////////////////////////////////////////////////////////////////////////////////////

//     public function fakultasIndex()
//     {
//         $data = Fakultas::all();
//         if (!$data) {
//             return response()->json(['status' => Response::HTTP_NOT_FOUND, 'pesan' => 'Data Tidak Ditemukan']);
//         }

//         return response()->json(['status' => Response::HTTP_OK, 'data' => $data]);
//     }

//     public function fakultasCreate(Request $request)
//     {
//         $this->validate($request,[
//             'nama' => 'required|string',
//             'longitude' => 'required|string',
//             'latitude' => 'required|string'
//         ]);

//         $fakultasData = $request->only('nama', 'longitude', 'latitude');
//         try{
//             $fakultas = Fakultas::create($fakultasData);
//             return response()->json([
//                 'status' => Response::HTTP_CREATED,
//                 'pesan' => 'Data Berhasil Ditambahkan',
//                 'data' => $fakultas
//             ], Response::HTTP_CREATED);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
//                 'pesan' => 'Data Gagal Ditambahkan',
//                 'data' => $e->getMessage()
//             ], Response::HTTP_INTERNAL_SERVER_ERROR);
//         }
//     }

//     public function fakultasShow($id)
//     {
//         //
//         $data = Fakultas::where('id',$id)->get();
//         return response()->json($data);
//     }

//     public function fakultasUpdate(Request $request, $id)
//     {
//         //
//         Fakultas::where('id',$id)->update($request->all());
//         return response()->json("Data Sudah diupdate");
//     }

//     public function fakultasDestroy($id)
//     {
//         //
//         Fakultas::where('id',$id)->delete();
//         return response()->json("Data Sudah Dihapus");
//     }

// /////////////////////////////////////////////////////////////////////

//     public function blokIndex()
//     {
//         //
//         $bloks = Blok::with('fakultas')->get();
//         return response()->json($bloks);
//     }

//     public function blokCreate(Request $request)
//     {
//         //
//         $this->validate($request,[
//             'nama' => 'required|string',
//             'id_fakultas' => 'required|integer|exists:fakultas,id',
//             'longitude' => 'required|string',
//             'latitude' => 'required|string'
//         ]);

//         $blok =[
//             'nama' => $request->input('nama'),
//             'id_fakultas' => $request->input('id_fakultas'),
//             'longitude' => $request->input('longitude'),
//             'latitude' => $request->input('latitude')
//         ];

//         $data = Blok::create($blok);
//         if ($data) {
//             $result = [
//                 'status' => 200,
//                 'pesan' => 'Data Berhasil Ditambahkan',
//                 'data' => $blok
//             ];
//         }
//         else{
//             $result = [
//                 'status' => 400,
//                 'pesan' => 'Data Gagal Ditambahkan',
//                 'data' => ""
//             ];
//         }
//         return response()->json($result);
//     }

//     public function blokShow($id)
//     {
//         //
//         $data = Blok::where('id',$id)->get();
//         return response()->json($data);
//     }

//     public function blokUpdate(Request $request, $id)
//     {
//         //
//         Blok::where('id',$id)->update($request->all());
//         return response()->json("Data Sudah diupdate");
//     }

//     public function blokDestroy($id)
//     {
//         //
//         Blok::where('id',$id)->delete();
//         return response()->json("Data Sudah Dihapus");
//     }

// //////////////////////////////////////////////////////////////////////////////////////////

//     public function slotParkirIndex()
//     {
//         // $slots = Slot_Parkir::all();
//         // $availableSlots = Slot_Parkir::where('status', 'Kosong', true)->count();
//         // // $occupiedSlots = Slot_Parkir::where('status', 'Terisi', true)->count();
//         // $slotKosong = $availableSlots.'/'.count($slots);

//         // return response()->json([
//         //     'total_slot' => count($slots),
//         //     'slot_kosong' => $slotKosong,
//         //     // 'slot_tersedia' => $occupiedSlots,
//         // ]);
//         // Menggunakan distinct untuk menghindari data duplikat berdasarkan slot_name
//         $slots = Slot_Parkir::distinct('slot_name')->get(['slot_name', 'status', 'x', 'y', 'id_blok']);
//         $availableSlots = $slots->where('status', 'Kosong')->count();
//         $totalSlots = $slots->count();
//         $slotKosong = $availableSlots . '/' . $totalSlots;

//         return response()->json([
//             'total_slot' => $totalSlots,
//             'slot_kosong' => $slotKosong,
//         ]);
//     }

//     public function slotParkirCreate(Request $request)
//     {
//         $this->validate($request,[
//             'slot_name' => 'required|string',
//             'status' => 'required|in:Kosong,Terisi',
//             'x' => 'required|string',
//             'y' => 'required|string',
//             'id_blok' => 'required|string|exists:bloks,id'
//         ]);

//         $slot_parkir =[
//             'slot_name' => $request->input('slot_name'),
//             'status' => $request->input('status'),
//             'x' => $request->input('x'),
//             'y' => $request->input('y'),
//             'id_blok' => $request->input('id_blok')
//         ];

//         $data = Slot_Parkir::create($slot_parkir);
//         if ($data) {
//             $result = [
//                 'status' => 200,
//                 'pesan' => 'Data Berhasil Ditambahkan',
//                 'data' => $slot_parkir
//             ];
//         }
//         else{
//             $result = [
//                 'status' => 400,
//                 'pesan' => 'Data Gagal Ditambahkan',
//                 'data' => ""
//             ];
//         }
//         return response()->json($result);
//     }

//     public function slotParkirShow($id)
//     {
//         $data = Slot_Parkir::where('id',$id)->get();
//         return response()->json($data);
//     }

//     public function slotParkirUpdate(Request $request, $id)
//     {
//         Slot_Parkir::where('id',$id)->update($request->all());
//         return response()->json("Data Sudah diupdate");
//     }

//     public function slotParkirDestroy($id)
//     {
//         //
//         Slot_Parkir::where('id',$id)->delete();
//         return response()->json("Data Sudah Dihapus");
//     }

// ////////////////////////////////////////////////////////////////////////////////////

//     public function ParkirIndex()
//     {
//         // $parkir = Parkir::all();
//         // return response()->json($parkir);
//         // $parkir = Parkir::with(['slot__parkir, blok, fakultas'])->get();
//         // return response()->json($parkir);
//         return Parkir::with('slot__parkir.blok.fakultas')->get();
//     }

//     public function ParkirCreate(Request $request)
//     {
//         $this->validate($request,[
//             'plat_nomor' => 'required|string',
//             'nama_pemesan' => 'required|string',
//             'jenis_mobil' => 'required|string',
//             'tanggal_masuk' => 'required|date',
//             'tanggal_keluar' => 'required|date',
//             'id_slot' => 'required|integer|exists:slot__parkirs,id',
//             // 'id_user' => 'required|integer|exists:users,id'
//         ]);

//         // $currentDateTime = Carbon::now();
//         // $bookingDateTime = Carbon::parse($request->input('tanggal_masuk'))->subHour(); // Substract 1 hour from booking time
//         // if ($currentDateTime->greaterThan($bookingDateTime)) {
//         //     return response()->json('Pemesanan hanya bisa dilakukan 1 jam sebelumnya', 400);
//         // }

//         $existingParkir = Parkir::where('plat_nomor', $request->input('plat_nomor'))->first();
//         if ($existingParkir) {
//             return response()->json('Maaf, plat nomor yang Anda masukkan sudah terdaftar', 400);
//         }

//         $slotParkir = Slot_Parkir::findOrFail($request->input('id_slot'));
//         if ($slotParkir->status == 'Terisi') {
//             return response()->json('Maaf, slot parkir yang Anda pilih sudah terisi', 400);
//         }

//         // Ubah status slot parkir menjadi "terisi"
//         $slotParkir->status = 'Terisi';
//         $slotParkir->save();

//         $parkir =[
//             'plat_nomor' => $request->input('plat_nomor'),
//             'nama_pemesan' => $request->input('nama_pemesan'),
//             'jenis_mobil' => $request->input('jenis_mobil'),
//             'tanggal_masuk' => $request->input('tanggal_masuk'),
//             'tanggal_keluar' => $request->input('tanggal_keluar'),
//             'id_slot' => $request->input('id_slot'),
//             // 'id_user' => $request->input('id_user')
//         ];
//         // $parkir->save();
//         $data = Parkir::create($parkir);
//         if ($data) {
//             $result = [
//                 'pesan' => 'Data Berhasil Ditambahkan',
//                 'data' => $parkir
//             ];
//         }
//         else{
//             $result = [
//                 'pesan' => 'Data Gagal Ditambahkan',
//                 'data' => ""
//             ];
//         }
//         return response()->json($result);
//     }

//     public function ParkirShow($id)
//     {
//         $data = Parkir::where('id',$id)->get();
//         return response()->json($data);
//         // return $parkir->load('slot__parkir.blok.fakultas');
//     }

//     /**
//      * Update the specified resource in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \App\Models\Parkir  $parkir
//      * @return \Illuminate\Http\Response
//      */

//     public function ParkirUpdate(Request $request, $id)
//     {   
//         Parkir::where('id',$id)->update($request->all());
//         return response()->json("Data Sudah diupdate");
//     }

//     public function ParkirDestroy($id)
//     {
//         //
//         Parkir::where('id',$id)->delete();
//         return response()->json("Data Sudah Dihapus");
//     }

// ///////////////////////////////////////////////////////////////////////////////////////////

//     public function ReserveIndex()
//     {
//         //
//         $reserves = Reserve::with('parkir.slot__parkir.blok.fakultas')->get();

//         // Mengembalikan respons HTTP dengan data yang diperoleh dari query
//         return response()->json($reserves);
//     }

//     /**
//      * Show the form for creating a new resource.
//      *
//      * @return \Illuminate\Http\Response
//      */
//     public function ReserveCreate(Request $request)
//     {
//         //
//         $this->validate($request,[
//             'tanggal_masuk' => 'required|date',
//             'tanggal_keluar' => 'required|date',
//             'id_parkir' => 'required|integer|exists:parkirs,id',
//             'id_user' => 'required|integer|exists:users,id'
//         ]);

//         $reserve =[
//             'tanggal_masuk' => $request->input('tanggal_masuk'),
//             'tanggal_keluar' => $request->input('tanggal_keluar'),
//             'id_parkir' => $request->input('id_parkir'),
//             'id_user' => $request->input('id_user')
//         ];
//         $data = Reserve::create($reserve);
//         if ($data) {
//             $result = [
//                 'pesan' => 'Data Berhasil Ditambahkan',
//                 'data' => $reserve
//             ];
//         }
//         else{
//             $result = [
//                 'pesan' => 'Data Gagal Ditambahkan',
//                 'data' => ""
//             ];
//         }
//         return response()->json($result);
//     }

//     /**
//      * Display the specified resource.
//      *
//      * @param  int  $id
//      * @return \Illuminate\Http\Response
//      */
//     public function ReserveShow($id)
//     {
//         //
//         $data = Reserve::where('id',$id)->get();
//         return response()->json($data);
//         // return $reserve->load('parkir.slot__parkir.blok.fakultas');
//     }

//     /**
//      * Update the specified resource in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \App\Models\Reserve  $reserve
//      * @return \Illuminate\Http\Response
//      */
//     public function ReserveUpdate(Request $request, $id)
//     {
//         //
//         Reserve::where('id',$id)->update($request->all());
//         return response()->json("Data Sudah diupdate");
//     }

//     /**
//      * Remove the specified resource from storage.
//      *
//      * @param  \App\Models\Reserve  $reserve
//      * @return \Illuminate\Http\Response
//      */
//     public function ReserveDestroy($id)
//     {
//         //
//         Reserve::where('id',$id)->delete();
//         return response()->json("Data Sudah Dihapus");
//     }

//     public function downloadReserveShow($id)
//     {
//         // Cari data reservasi berdasarkan $id
//         $reservasi = Reserve::findOrFail($id);

//         // Buat objek Dompdf
//         $dompdf = new Dompdf();

//         // Atur opsi jika diperlukan (misalnya, ukuran dan orientasi halaman)
//         $options = new Options();
//         $options->set('defaultFont', 'Arial');
//         $dompdf->setOptions($options);

//         // Buat isi dokumen HTML untuk struk reservasi
//         $html = view('pdf.struk_reservasi', compact('reserve'))->render();

//         // Muat isi dokumen HTML ke Dompdf
//         $dompdf->loadHtml($html);

//         // Render PDF
//         $dompdf->render();

//         // Atur nama file PDF yang akan diunduh
//         $fileName = 'struk_reservasi_' . $reservasi->id . '.pdf';

//         // Unduh file PDF
//         return $dompdf->stream($fileName);
//     }

// /////////////////////////////////////////////////////////////////////////////////////////////

//     public function monitorKendaraanIndex()
//     {
//         //
//         $monitorKendaraan = Monitor::with('parkir.slot__parkir')->get();
//         if ($monitorKendaraan->isEmpty()) {
//             return response()->json(['message' => 'Data not found.'], 404);
//         }
//         return response()->json($monitorKendaraan);
//     }

//     public function monitorKendaraanCreate(Request $request)
//     {
//         //
//         $this->validate($request,[
//             'id_parkir' => 'required|string|exists:parkirs,id',
//             'id_slot' => 'required|string|exists:slot__parkirs,id'
//         ]);

//         $monitor =[
//             'id_parkir' => $request->input('id_parkir'),
//             'id_slot' => $request->input('id_slot')
//         ];
//         $data = Monitor::create($monitor);
//         if ($data) {
//             $result = [
//                 'pesan' => 'Data Berhasil Ditambahkan',
//                 'data' => $monitor
//             ];
//         }
//         else{
//             $result = [
//                 'pesan' => 'Data Gagal Ditambahkan',
//                 'data' => ""
//             ];
//         }
//         return response()->json($result);
//     }

//     public function monitorKendaraanShow($id)
//     {
//         $data = Monitor::with(['parkir', 'slotParkir'])->where('id', $id)->first();
//         return response()->json($data);
//     }

//     public function monitorKendaraanUpdate(Request $request, $id)
//     {
//         //
//         Monitor::where('id',$id)->update($request->all());
//         return response()->json("Data Sudah diupdate");
//     }

//     public function monitorKendaraanDestroy($id)
//     {
//         //
//         Monitor::where('id',$id)->delete();
//         return response()->json("Data Sudah Dihapus");
//     }

//     // public function checkExpiredBookings ()
//     // {
//     //     $currentDateTime = Carbon::now();
//     //     $expiredBookingTime = Parkir::where('status', 'Berlangsung')->where('tanggal_keluar', '<', $currentDateTime)->get();

//     //     foreach ($expiredBookingTime as $booking)
//     //     {
//     //         $booking->status('Selesai');
//     //         $booking->save();

//     //         $slot = $booking->slot;
//     //         $slot->status = 'Kosong';
//     //         $slot->save();
//     //     }
//     //     return response()->json('Parkir Selesai');
//     // }
}