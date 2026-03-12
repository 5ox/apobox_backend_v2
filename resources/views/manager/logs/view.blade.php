@extends('layouts.manager')
@section('title', 'Logs - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Application Logs" />

<x-form-section>
    <div class="mb-3">
        <a href="/{{ $prefix }}/logs/view/email" class="btn btn-sm btn-outline-secondary @if($log === 'email') active @endif"><i data-lucide="mail" class="icon-sm me-1"></i>Email</a>
        <a href="/{{ $prefix }}/logs/view/laravel" class="btn btn-sm btn-outline-secondary @if($log === 'laravel') active @endif"><i data-lucide="file-text" class="icon-sm me-1"></i>Laravel</a>
    </div>
</x-form-section>

<x-detail-card title="{{ ucfirst($log) }} Log">
    <div class="log-viewer">
        <pre>{{ $logFile }}</pre>
    </div>
</x-detail-card>
@endsection
