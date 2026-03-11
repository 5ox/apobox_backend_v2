<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') - APO Box</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    @vite(['resources/sass/global.scss'])
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 text-center">
                @yield('content')
                <p class="mt-4"><a href="/" class="btn btn-primary">Return Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
