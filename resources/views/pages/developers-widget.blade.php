@extends('layouts.default')
@section('title', $title . ' - APO Box')
@section('content')
<h2>{{ $title }}</h2>
<div class="developer-docs">
    {!! $content !!}
</div>
@endsection
