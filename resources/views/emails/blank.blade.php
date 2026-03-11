@extends('layouts.email')

@section('content')
<p>{{ $name }},</p>
{!! $body !!}
@endsection
