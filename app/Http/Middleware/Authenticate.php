<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $isAuthenticatedAdmin = $request->session();
        //This will be executed if the new authentication fails.
        if ($isAuthenticatedAdmin->get('logged_in') !== 'konan') {
            return redirect()->route('admin.login')->with('message', 'Authentication Error.');
        }

        return $next($request);
    }
}