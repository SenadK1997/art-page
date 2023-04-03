<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use App\Models\Admins;

class AdminUserProvider extends ServiceProvider
{
    public function boot()
    {
        // $this->registerPolicies();

        Auth::provider('admins', function ($app, array $config) {
            return new AdminUserProvider($app['hash'], $config['model']);
        });
    }

    public function validateCredentials($user, array $credentials)
    {
        return Hash::check($credentials['password'], $user->password);
    }

    public function retrieveByUsername($username)
    {
        return Admins::where('username', $username)->first();
    }
}
