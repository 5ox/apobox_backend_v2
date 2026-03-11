@extends('layouts.email')

@section('content')
<h2>{{ $customerName }},</h2>
<p>Your credit card has expired.</p>
<p>To avoid any interruption of service, please click the link below to update your payment information.</p>
<p><a href="{{ $updatePaymentUrl }}">{{ $updatePaymentUrl }}</a></p>
@endsection
