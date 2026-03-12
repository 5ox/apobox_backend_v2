<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'APO Box Admin')</title>
    <link rel="icon" type="image/png" href="/images/icon.png">
    <link rel="apple-touch-icon" href="/images/icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/sass/global.scss', 'resources/sass/admin.scss'])
    @stack('styles')
</head>
<body data-module="@yield('module')">
    @include('partials.admin-navbar')

    <div class="d-flex">
        @include('partials.admin-sidebar')

        <main class="flex-grow-1 p-4" style="min-height: calc(100vh - 56px); overflow-x: auto;">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
