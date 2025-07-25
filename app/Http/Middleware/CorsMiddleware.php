<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
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
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE, PATCH',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN, X-Socket-ID',
            'Access-Control-Expose-Headers'    => 'Authorization, X-Total-Count'
        ];

        // Handle preflight OPTIONS requests
        if ($request->isMethod('OPTIONS'))
        {
            return response()->json(null, 200, $headers);
        }

        $response = $next($request);
        
        // Add CORS headers to response
        foreach($headers as $key => $value)
        {
            $response->header($key, $value);
        }

        // Add additional security headers
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}