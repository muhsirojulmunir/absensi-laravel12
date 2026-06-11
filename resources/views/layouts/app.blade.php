<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_record.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo_record.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'JMN Matrix') }}</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {font-family: 'Plus Jakarta Sans', sans-serif;}
    </style>
</head>
<body class="bg-[#0f172a] min-h-screen flex items-center justify-center">
    @yield('content')
</body>
</html>
