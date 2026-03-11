@extends('layouts.email')

@section('content')
<p>{{ $firstName }} {{ $lastName }},</p>
<p>Your APO Box order #<strong>{{ $orderId }}</strong> has shipped!</p>
@if(!empty($outboundTracking))
<p>Outbound tracking #: <strong><a href="{{ $trackingUrl }}{{ $outboundTracking }}">{{ $outboundTracking }}</a></strong></p>
@endif
@if(!empty($inboundTracking))
<p>Inbound tracking #: <strong>{{ $inboundTracking }}</strong></p>
@endif
@endsection
