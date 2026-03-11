<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .email-header { background-color: #1a4b8c; color: #fff; padding: 20px; text-align: center; }
        .email-header h1 { margin: 0; font-size: 24px; }
        .email-body { padding: 20px; background-color: #f9f9f9; }
        .email-footer { padding: 15px; text-align: center; font-size: 12px; color: #888; }
        a { color: #1a4b8c; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>APO Box</h1>
        </div>
        <div class="email-body">
            @yield('content')
        </div>
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} APO Box. All rights reserved.</p>
            <p><a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>
        </div>
    </div>
</body>
</html>
