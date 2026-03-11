@extends('layouts.email')

@section('content')
<p>{{ $firstName }} {{ $lastName }},</p>
<p>Your credit card expires soon. Please make sure to update your payment information before your credit card expires next month.</p>
@endsection
