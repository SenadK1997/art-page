@extends('layouts.admin')

@section('title')
    Dashboard
@endsection

@section('content')
<h1>Admin Dashboard</h1>

<p>Welcome, {{ Auth::user()->name }}!</p>

<form method="POST" action="{{ route('admin.logout') }}">
    @csrf

    <button type="submit">Logout</button>
</form>

@endsection