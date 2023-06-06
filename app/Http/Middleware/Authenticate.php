<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // return $request->expectsJson() ? null : route('login');
        // Check if the user has a valid access token
        if ($request->user() && $request->user()->token()) {
            return null; // User has a valid token, allow access
        }
        // dd($request->user()->token());
        // User does not have a valid token, redirect to login
        return route('login');
    }
}