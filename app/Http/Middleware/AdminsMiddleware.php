<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
{
    Log::info('AdminsMiddleware handle start');

    // dd($request->session()->all());
    // dd(Auth::guard('admins')->user());
    // dd(Auth::guard('admins')->check());
    // if (Auth::guard('admins')->check()) {
        // return $next($request);
    // }
    // return redirect()->route('admin.login');
    return $next($request);
}

}

// else if ($request->route()->getName() === 'admin.login') {
//     return $next($request);
// } 