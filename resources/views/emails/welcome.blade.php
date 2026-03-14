@extends('layouts.email')

@section('content')
<h2>You're almost finished!</h2>
<p><a href="{{ $almostFinishedUrl }}">Add your billing information to ensure fast delivery!</a></p>
<p>Your new U.S. address has been created and is ready for immediate use.</p>

<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 20px 0;">
<tr>
<td width="48%" valign="top" style="padding-right: 8px;">
    <div style="border: 1px solid #93c5fd; border-radius: 12px; padding: 20px; background-color: #eff6ff;">
        <div style="font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; color: #1a5fb4; margin-bottom: 12px;">
            &#x1F4E6; You Ship Packages Here
        </div>
        <div style="font-weight: 600; color: #0f172a; line-height: 1.6;">
            {{ $firstName }} {{ $lastName }}<br>
            {{ $billingId }}<br>
            1911 Western Ave<br>
            Plymouth, IN 46563
        </div>
        <div style="font-size: 12px; color: #64748b; margin-top: 10px;">Ship your packages to this address. Include your Billing ID so we can identify them when they arrive.</div>
    </div>
</td>
<td width="48%" valign="top" style="padding-left: 8px;">
    <div style="border: 1px solid #86efac; border-radius: 12px; padding: 20px; background-color: #f0fdf4;">
        <div style="font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; color: #16a34a; margin-bottom: 12px;">
            &#x1F69A; We Forward Them Here
        </div>
        <div style="font-weight: 600; color: #0f172a; line-height: 1.6;">
            {{ $firstName }} {{ $lastName }}<br>
@if(!empty($address['entry_company'])){{ $address['entry_company'] }}<br>@endif
{{ $address['entry_street_address'] }}<br>
@if(!empty($address['entry_suburb'])){{ $address['entry_suburb'] }}<br>@endif
{{ $address['entry_city'] }}, {{ $address['zone_code'] ?? '' }} {{ $address['entry_postcode'] }}
        </div>
        <div style="font-size: 12px; color: #64748b; margin-top: 10px;">This is where we forward your packages after processing.</div>
    </div>
</td>
</tr>
</table>

<p>We look forward to serving your shipping needs.</p>
@endsection
