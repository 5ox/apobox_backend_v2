@extends('layouts.email')

@section('content')
<h2>{{ $customerName }},</h2>
<p>You have a package that is awaiting payment.</p>
<p>Payment for your APO Box order #<strong>{{ $orderId }}</strong> could not be completed. Please click the link below to complete your payment.</p>
<p><a href="{{ $payUrl }}">{{ $payUrl }}</a></p>
@if(!empty($comments))<p>{{ $comments }}</p>@endif
@endsection
