<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'APO Box Account')</title>
    <link rel="icon" type="image/png" href="/images/icon.png">
    <link rel="apple-touch-icon" href="/images/icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/sass/global.scss', 'resources/sass/public.scss'])
    @stack('styles')
</head>
<body data-module="@yield('module')">
    @include('partials.navbar')

    <main class="container py-4">
        @include('partials.flash')
        @yield('content')
    </main>

    @include('partials.footer')

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
