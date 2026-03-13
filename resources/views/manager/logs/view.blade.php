@extends('layouts.manager')
@section('title', 'Logs - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Application Logs" />

<x-form-section>
    <div class="mb-3 d-flex flex-wrap gap-2">
        @foreach($channels as $key => $ch)
            <a href="/{{ $prefix }}/logs/view/{{ $key }}"
               class="btn btn-sm btn-outline-secondary @if($log === $key) active @endif">
                <i data-lucide="{{ $ch['icon'] }}" class="icon-sm me-1"></i>{{ $ch['label'] }}
            </a>
        @endforeach
    </div>
</x-form-section>

<x-detail-card title="{{ $channels[$log]['label'] ?? ucfirst($log) }} Log">
    <div class="log-viewer">
        <pre>{{ $logFile }}</pre>
    </div>
</x-detail-card>
@endsection
