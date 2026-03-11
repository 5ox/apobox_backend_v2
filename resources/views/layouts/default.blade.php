<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'APO Box Account')</title>

    @if(config('apobox.cdn_enabled', false))
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    @else
        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    @endif

    @vite(['resources/sass/global.scss', 'resources/sass/public.scss'])

    @stack('styles')
</head>
<body>
    @include('partials.navbar')

    <div class="container">
        @include('partials.flash')
        @yield('content')
    </div>

    @include('partials.footer')

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
