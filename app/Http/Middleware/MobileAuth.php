<?php

namespace App\Http\Middleware;

use Closure;
use App\Anggota;
use App\Support\ApiResponse;

class MobileAuth
{
    /**
     * Handle an incoming request for Mobile API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        
        if (empty($token)) {
            $token = $request->bearerToken();
        } else {
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
        }
        
        if (empty($token)) {
            // Maybe they passed token in query param or body
            $token = $request->input('token');
        }
        
        if (empty($token)) {
            return ApiResponse::error('Unauthorized. Token is missing.', 401);
        }

        $anggota = Anggota::where('token', $token)->first();

        if (empty($anggota)) {
            return ApiResponse::error('Unauthorized. Invalid token.', 401);
        }

        // Set the authenticated user's no_anggota so controllers don't have to rely on client input
        $request->merge(['no_anggota' => $anggota->no_anggota]);

        return $next($request);
    }
}
