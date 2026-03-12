<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') - APO Box</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }
        .error-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            padding: 3rem 2rem;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 700;
            color: #0f2b5b;
            line-height: 1;
        }
        .error-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #0f2b5b;
            margin-top: 1rem;
        }
        .error-message {
            color: #475569;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center" style="min-height: 100vh; align-items: center;">
            <div class="col-md-5 text-center">
                <div class="error-card">
                    @yield('content')
                    <p class="mt-4 mb-0">
                        <a href="/" class="btn btn-primary px-4">
                            Return Home
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
