<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Operator;
use App\Models\TokenOperator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class OperatorController extends Controller
{
    public function registerOperator(Request $request)
    {
        $nama = $request->nama;
        $email = $request->email;
        $password = $request->password;
        $phone = $request->phone;

        if (empty($nama) || empty($email) || empty($password) || empty($phone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semua field harus diisi'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format email tidak valid'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Cek apakah email sudah terdaftar
        if (Operator::where('email', $email)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email sudah terdaftar'
            ], Response::HTTP_CONFLICT);
        }

        // Validasi nomor telepon
        if (!is_numeric($phone) || strlen($phone) > 12) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor telepon tidak valid (maksimal 12 digit)'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $operator = Operator::create([
                'nama' => $nama,
                'email' => $email,
                'password' => Hash::make($password),
                'phone' => $phone
            ]);

            $credentials = ['email' => $email, 'password' => $password];
            $token = Auth::guard('operator')->attempt($credentials);

            $expiredAt = Carbon::now()->addHour();

            TokenOperator::create([
                'id_operator' => $operator->id,
                'api_token' => $token,
                'expired_at' => $expiredAt,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Operator berhasil didaftarkan',
                'data' => [
                    'operator' => $operator,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => Auth::guard('operator')->factory()->getTTL() * 60
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mendaftarkan operator',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function loginOperator(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        if (empty($email) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields'], Response::HTTP_BAD_REQUEST);
        }

        $credentials = ['email' => $email, 'password' => $password];

        $operator = Operator::where('email', $email)->first();
        if (!$operator) {
            return response()->json(['status' => 'error', 'message' => 'Email entered is not registered or incorrect'], Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($password, $operator->password)) {
            return response()->json(['status' => 'error', 'message' => 'Password is incorrect'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$token = Auth::guard('operator')->attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $operator = Auth::guard('operator')->user();

        $expiredAt = Carbon::now()->addHour();

        $existingToken = TokenOperator::where('id_operator', $operator->id)->first();

        if ($existingToken) {
            $existingToken->api_token = $token;
            $existingToken->expired_at = $expiredAt;
            $existingToken->save();
        } else {
            TokenOperator::create([
                'id_operator' => $operator->id,
                'api_token' => $token,
                'expired_at' => $expiredAt,
            ]);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('operator')->factory()->getTTL() * 60
        ]);
    }
}
