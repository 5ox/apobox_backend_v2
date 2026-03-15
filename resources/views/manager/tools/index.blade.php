@extends('layouts.manager')
@section('title', 'Tools - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Tools" subtitle="Run maintenance commands" />

{{-- Flash output from last command --}}
@if(session('tool_output'))
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i data-lucide="terminal" class="icon"></i> Command Output</strong>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="this.closest('.card').remove()">
                <i data-lucide="x" class="icon"></i> Dismiss
            </button>
        </div>
        <div class="card-body p-0">
            <pre class="m-0 p-3" style="white-space: pre-wrap; font-size: .85rem; max-height: 400px; overflow-y: auto; background: #1e1e2e; color: #cdd6f4; border-radius: 0 0 .5rem .5rem;">{{ session('tool_output') }}</pre>
        </div>
    </div>
@endif

{{-- Postage Calculator --}}
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route($prefix . '.tools.postage-calculator') }}" class="card text-decoration-none border-primary border-opacity-25 shadow-sm" style="transition: box-shadow .15s;">
            <div class="card-body d-flex align-items-center py-3">
                <div class="rounded-2 bg-primary bg-opacity-10 p-2 me-3">
                    <i data-lucide="calculator" class="text-primary" style="width:24px;height:24px;"></i>
                </div>
                <div class="flex-fill">
                    <h6 class="mb-0 text-body">USPS Postage Calculator</h6>
                    <p class="text-muted small mb-0">Compare retail vs corporate USPS rates side by side</p>
                </div>
                <i data-lucide="chevron-right" class="text-muted" style="width:20px;height:20px;"></i>
            </div>
        </a>
    </div>
</div>

<h6 class="text-muted text-uppercase small fw-semibold mb-3">Maintenance Commands</h6>
<div class="row g-3">
    @foreach($commands as $key => $cmd)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start mb-2">
                        <div class="rounded-2 bg-primary bg-opacity-10 p-2 me-3">
                            <i data-lucide="{{ $cmd['icon'] }}" class="text-primary" style="width:24px;height:24px;"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">{{ $cmd['label'] }}</h6>
                            <p class="text-muted small mb-0">{{ $cmd['description'] }}</p>
                        </div>
                    </div>
                    <div class="mt-auto pt-3">
                        <form method="POST" action="{{ route($prefix . '.tools.run', $key) }}"
                              @if($cmd['confirm']) onsubmit="return confirm('{{ $cmd['confirm'] }}')" @endif>
                            @csrf
                            <button type="submit" class="btn btn-sm {{ ($cmd['confirm'] ?? false) ? 'btn-warning' : 'btn-primary' }} w-100">
                                <i data-lucide="play" class="icon"></i> Run
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach


</div>
@endsection
