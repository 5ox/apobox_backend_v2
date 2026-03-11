@extends('layouts.email')

@section('content')
<p>Your APO Box order #<strong>{{ $orderId }}</strong> has failed automatic payment.</p>
<p>Your credit card on file has failed. Please click the link below to pay for your shipment.</p>
<p><a href="{{ $payUrl }}">{{ $payUrl }}</a></p>
<p>We will retry with the payment information on file within 24 hours.</p>
@if(!empty($comments))<p>{{ $comments }}</p>@endif
@endsection
