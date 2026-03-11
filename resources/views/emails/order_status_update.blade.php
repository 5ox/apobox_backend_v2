@extends('layouts.email')

@section('content')
<p>{{ $firstName }} {{ $lastName }},</p>
<p>Your APO Box order #<strong>{{ $orderId }}</strong> has been updated. The status is now "{{ ucfirst($status) }}".</p>
@if(!empty($comments))<p>{{ $comments }}</p>@endif
@endsection
