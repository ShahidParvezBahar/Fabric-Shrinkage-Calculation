<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Fabric Shrinkage Management & Group Classification SPA</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <script>
        window.AppConfig = {
            baseUrl: "{{ url('/') }}",
            apiUrl: "{{ url('/api') }}"
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-800 antialiased bg-slate-50 font-sans selection:bg-indigo-500 selection:text-white">
    <div id="app"></div>
</body>
</html>
