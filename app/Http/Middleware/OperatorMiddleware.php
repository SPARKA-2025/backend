<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OperatorMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('operator')->check()) {
            return $next($request);
        }
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }
}