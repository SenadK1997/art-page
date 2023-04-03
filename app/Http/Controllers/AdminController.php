<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admins;


class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function showLoginForm()
    {
        return view('admin.login');
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = Admins::where('username', $credentials['username'])->first();
        // dd(Auth::guard('admins')->attempt($credentials));
        if (Auth::guard('admins')->attempt($credentials)) {
            $user = Auth::guard('admins')->user();
            Auth::guard('admins')->login($user);
            
            if (Auth::guard('admins')->check()) {
                
                return redirect()->route('admin.dashboard');
            }
        }

        // return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();
        return 'kurac';
    }
    public function logout()
    {
        Auth::guard('admins')->logout();

        return redirect()->route('admin.login');
    }
}

// public function login(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'username' => ['required', 'string'],
    //         'password' => ['required', 'string'],
    //     ]);
    //     $admin = new Admins([
    //         'username' => $request->input('username'),
    //         'password' => Hash::make($request->input('password')),
    //     ]);
    
    //     $admin->save();

    //     dd(Auth::guard('admins')->attempt($credentials));
    //     if (Auth::guard('admins')->attempt($credentials)) {
    //         return redirect()->route('admin.dashboard');
    //         return view('/about');
    //     }
    //     return 'pusi kurac';
    //     return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();
    //     dd('sucmadik');
    // }
