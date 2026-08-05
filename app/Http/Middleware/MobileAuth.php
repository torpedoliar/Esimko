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
        $token = $request->bearerToken();

        if (empty($token)) {
            return ApiResponse::error('Unauthorized. Token is missing.', 401);
        }

        $anggota = Anggota::where('token', $token)->first();

        if (empty($anggota)) {
            return ApiResponse::error('Unauthorized. Invalid token.', 401);
        }

        // Token expiry: rotate login_at on login; invalid after 30 days
        if (!empty($anggota->login_at) && strtotime($anggota->login_at) < strtotime('-30 days')) {
            return ApiResponse::error('Sesi telah berakhir. Silahkan login kembali.', 401);
        }

        // Set the authenticated user's no_anggota so controllers don't have to rely on client input
        $request->merge(['no_anggota' => $anggota->no_anggota]);

        return $next($request);
    }
}
