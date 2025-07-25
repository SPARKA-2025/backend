<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Token;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $nama = $request->nama;
        $email = $request->email;
        $password = $request->password;
        //alamat = $request->alamat;
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
            return response()->json(['status' => 'error', 'message' => 'Password should be min 8 character'], Response::HTTP_BAD_REQUEST);
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

        // Check if plat_nomor is valid (optional but if provided, max 10 characters)
        if (!empty($plat_nomor) && strlen($plat_nomor) > 10) {
            return response()->json(['status' => 'error', 'message' => 'License plate number must not exceed 10 characters'], Response::HTTP_BAD_REQUEST);
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
            return response()->json(['status' => 'error', 'message' => 'Email entered is not registered or incorrect'], Response::HTTP_UNAUTHORIZED);
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

        return $this->respondWithToken($token, $user);
    }

    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'id' => $user->id,
            'access_token' => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth()->factory()->getTTL() * 60 // 1 hour in seconds
            'expires_in' => JWTAuth::factory()->getTTL() * 60 // 1 hour in seconds
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['status' => 'success', 'message' => 'Successfully logged out'], Response::HTTP_OK);
    }
}
