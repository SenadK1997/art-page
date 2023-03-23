<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.cdnfonts.com/css/futura-pt" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
</head>
<body>
    <div class="flex flex-col justify-between h-full">
        <div>
            @include('partials.navbar')
        </div>
        <div class="content__height justify-center items-baseline flex">
            @yield('content')
        </div>
        <div>
            @include('partials.footer')
        </div>
    </div>
</body>
<script src="{{ asset('assets/js/navbar.js') }}"></script>
</html>