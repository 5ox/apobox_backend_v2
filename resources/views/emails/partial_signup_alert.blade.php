@extends('layouts.email')

@section('content')
<h2>{{ $customerName }},</h2>
<p>Your APO Box registration is incomplete.</p>
<p>There are no addresses saved with your account. To complete your registration, please click the link below to add an address.</p>
<p><a href="{{ $addAddressUrl }}">{{ $addAddressUrl }}</a></p>
@endsection
