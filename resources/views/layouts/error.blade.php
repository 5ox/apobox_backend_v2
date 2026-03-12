<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') - APO Box</title>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
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
