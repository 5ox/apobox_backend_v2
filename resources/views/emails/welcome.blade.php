@extends('layouts.email')

@section('content')
<h2>You're almost finished!</h2>
<p><a href="{{ $almostFinishedUrl }}">Add your billing information to ensure fast delivery!</a></p>
<p>Your new U.S. address has been created and is ready for immediate use.</p>
<table style="border-collapse: collapse; margin: 0 auto; width: 100%">
<tr>
<td style="background: #f2f2f2; border: 1px solid #d9d9d9; padding: 10px; width: 48%">
<h3>You Ship Packages Here:</h3>
<address>{{ $firstName }} {{ $lastName }}<br>Attn: {{ $billingId }}<br>1911 Western Ave<br>Plymouth, IN 46563</address>
</td>
<td style="width: 4%">&nbsp;</td>
<td style="background: #f2f2f2; border: 1px solid #d9d9d9; padding: 10px; width: 48%">
<h3>We Forward Them Here:</h3>
<address>{{ $firstName }} {{ $lastName }}<br>
@if(!empty($address['entry_company'])){{ $address['entry_company'] }}<br>@endif
{{ $address['entry_street_address'] }}<br>
@if(!empty($address['entry_suburb'])){{ $address['entry_suburb'] }}<br>@endif
{{ $address['entry_city'] }}, {{ $address['zone_code'] ?? '' }} {{ $address['entry_postcode'] }}</address>
</td>
</tr>
</table>
<p>We look forward to serving your shipping needs.</p>
@endsection
