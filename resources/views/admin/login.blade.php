@extends('layouts.admin')

@section('title')
    Login
@endsection

@section('content')
<div class="flex justify-center items-center min-h-screen">
    <div class="bg-white rounded-lg p-8">
        <h1 class="text-2xl font-medium mb-4">Admin Login</h1>

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <div class="mb-4">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" class="border border-gray-400 p-2 w-full" required>
                @error('username')
                <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="border border-gray-400 p-2 w-full" required>
                @error('password')
                <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Login</button>
            </div>
        </form>
    </div>
</div>
@endsection

