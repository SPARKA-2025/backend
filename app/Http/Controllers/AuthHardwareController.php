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

class AuthHardwareController extends Controller
{
    public function registerEksklusif(Request $request)
    {
        $nama = $request->nama;
        $email = $request->email;
        $password = $request->password;
        $alamat = $request->alamat;
        $phone = $request->phone;

        // Check if field is empty
        if (empty($nama) or empty($email) or empty($password) or empty($alamat) or empty($phone)) {
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
            $user->alamat = $request->alamat;
            $user->phone = $request->phone;

            if ($user->save()) {
                return $this->loginEksklusif($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function loginEksklusif(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        // Check if fields are empty
        if (empty($email) || empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields'], Response::HTTP_BAD_REQUEST);
        }

        // Check if the user exists with the provided email
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Email is not registered'], Response::HTTP_UNAUTHORIZED);
        }

        // Verify the password
        if (!Hash::check($password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Password is incorrect'], Response::HTTP_UNAUTHORIZED);
        }

        $credentials = request(['email', 'password']);

        // Attempt to generate token
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $user = JWTAuth::user();

        // Set token expiration to 10 years
        JWTAuth::factory()->setTTL(5256000);
        $hardwareToken = JWTAuth::fromUser($user);

        $expiredAt = Carbon::now()->addYears(10); // Set expiration to 10 years

        $existingToken = Token::where('id_user', $user->id)->first();

        // Save token in the database
        if ($existingToken) {
            $existingToken->api_token = $hardwareToken;
            $existingToken->expired_at = $expiredAt;
            $existingToken->save();
        } else {
            Token::create([
                'id_user' => $user->id,
                'api_token' => $hardwareToken,
                'expired_at' => $expiredAt,
            ]);
        }

        return $this->respondWithTokenHardware($hardwareToken);
    }

    protected function respondWithTokenHardware($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 5256000 * 60 // 10 years in seconds
        ]);
    }

    public function logoutEksklusif()
    {
        auth()->logout();
        return response()->json(['status' => 'success', 'message' => 'Successfully logged out'], Response::HTTP_OK);
    }
}