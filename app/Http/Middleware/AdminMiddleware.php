<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            // Set the guard to admin for JWT
            Auth::shouldUse('admin');
            
            // Try to authenticate the user with JWT
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['status' => 'error', 'message' => 'Maaf, Halaman Ini Hanya Dapat Diakses Oleh Admin'], 403);
            }
            
            // Set the authenticated user for the admin guard
            Auth::guard('admin')->setUser($user);
            
        } catch (TokenExpiredException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token is required'], 401);
        }

        return $next($request);
    }
}