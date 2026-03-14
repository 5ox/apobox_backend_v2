<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
    <style>
        /* Reset */
        body, table, td, p, a, li { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }

        /* Base */
        body {
            margin: 0; padding: 0; width: 100%; min-width: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 15px; color: #0f172a; line-height: 1.7;
            background-color: #f1f5f9;
            -webkit-font-smoothing: antialiased;
        }

        /* Wrapper */
        .email-wrapper { background-color: #f1f5f9; padding: 40px 20px; }

        /* Card */
        .email-card {
            max-width: 560px; margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        /* Header */
        .email-header {
            padding: 32px 32px 24px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .email-header img { height: 44px; width: auto; }

        /* Body */
        .email-body {
            padding: 32px;
            color: #0f172a;
            font-size: 15px;
            line-height: 1.7;
        }
        .email-body p { margin: 0 0 16px 0; }
        .email-body p:last-child { margin-bottom: 0; }
        .email-body h2 { margin: 0 0 16px 0; font-size: 20px; font-weight: 600; color: #0f2b5b; }
        .email-body h3 { margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #0f2b5b; }
        .email-body a { color: #1a5fb4; }
        .email-body strong { font-weight: 600; color: #0f2b5b; }

        /* Footer */
        .email-footer {
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
        .email-footer p { margin: 0 0 4px 0; }
        .email-footer a { color: #64748b; text-decoration: none; }

        /* Table styling for welcome email */
        .email-body table { width: 100%; }
        .email-body td { vertical-align: top; }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 12px; }
            .email-body { padding: 24px 20px; }
            .email-header { padding: 24px 20px 20px; }
            .email-footer { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td align="center">
                    <div class="email-card">
                        <div class="email-header">
                            <img src="{{ config('app.url') }}/images/logo.png" alt="APO Box" height="44">
                        </div>
                        <div class="email-body">
                            @yield('content')
                        </div>
                        <div class="email-footer">
                            <p>&copy; {{ date('Y') }} APO Box. All rights reserved.</p>
                            <p><a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
