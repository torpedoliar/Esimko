<?php

namespace App\Http\Middleware;

use Closure;

class CekLogin
{
    public function handle($request, Closure $next)
    {
        if (!session()->has('useractive')) return redirect('auth/login');
        return $next($request);
    }
}
