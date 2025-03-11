<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.cdnfonts.com/css/futura-pt" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script src="https://www.google.com/recaptcha/enterprise.js?render=6LeMiPAqAAAAAGzwmmLNfG2kVlUNFqAJoR-mb4iK"></script>
</head>
<body>
    @yield('content')
</body>
@stack('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.4/flowbite.min.js"></script>
<script>
  function onClick(e) {
    e.preventDefault();
    grecaptcha.enterprise.ready(async () => {
      const token = await grecaptcha.enterprise.execute('6LeMiPAqAAAAAGzwmmLNfG2kVlUNFqAJoR-mb4iK', {action: 'Submit'});
    });
  }
</script>
</html>