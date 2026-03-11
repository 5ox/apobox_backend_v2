@extends('layouts.email')

@section('content')
<p>{{ $customerName }},</p>
<p>If you recently requested to close your account, please click the link below.</p>
<p>Closing your account will immediately deactivate your APO Box address and remove your credit card information from our system.</p>
<p><strong>Important:</strong> You must be logged into your APO Box account to close your account.</p>
<p><a href="{{ $url }}">{{ $url }}</a></p>
<p>If you did not request to close your account, you may still log in normally.</p>
@endsection
