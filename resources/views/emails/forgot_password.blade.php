@extends('layouts.email')

@section('content')
<p>{{ $customerName }},</p>
<p>If you recently requested to reset your password, please click the link below.</p>
<p><a href="{{ $url }}">{{ $url }}</a></p>
<p>If you did not request to reset your password, you do not need to click the link. Your password is safe.</p>
@endsection
