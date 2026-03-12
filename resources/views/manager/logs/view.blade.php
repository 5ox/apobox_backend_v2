@extends('layouts.manager')
@section('title', 'Logs - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Log Viewer</h2>
<div class="mb-3">
    <a href="/{{ $prefix }}/logs/view/email" class="btn btn-sm btn-outline-secondary @if($log === 'email') active @endif">Email</a>
    <a href="/{{ $prefix }}/logs/view/laravel" class="btn btn-sm btn-outline-secondary @if($log === 'laravel') active @endif">Laravel</a>
</div>
<pre class="bg-dark text-light p-3 rounded" style="max-height: 600px; overflow-y: auto; font-size: 0.8rem;">{{ $logFile }}</pre>
@endsection
