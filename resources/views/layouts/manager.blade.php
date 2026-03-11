<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'APO Box Admin')</title>

    @if(config('apobox.cdn_enabled', false))
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    @else
        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    @endif

    @vite(['resources/sass/global.scss', 'resources/sass/admin.scss'])

    @stack('styles')
</head>
<body>
    @include('partials.admin-navbar')

    <div class="container-fluid">
        <div class="row">
            @include('partials.admin-sidebar')

            <main class="col-md-10 ms-sm-auto px-md-4">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
