@extends('layouts.manager')
@section('title', 'Edit ' . $meta['label'] . ' Template - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Edit Template: {{ $meta['label'] }}" subtitle="{{ $meta['description'] }}" />

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Template Body</strong>
                <div class="d-flex align-items-center gap-2">
                    @if($customized)
                        <span class="app-tag app-tag--primary">Customized</span>
                    @else
                        <span class="app-tag app-tag--secondary">Default</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="/{{ $prefix }}/settings/email-templates/{{ $key }}">
                    @csrf
                    @if($errors->any())<div class="alert alert-danger mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

                    <div class="mb-3">
                        <label class="form-label">Subject Line Override</label>
                        <input type="text" name="subject" class="form-control font-monospace"
                               value="{{ old('subject', $subject) }}"
                               placeholder="{{ $meta['default_subject'] }}">
                        <div class="form-text">Leave blank to use the default subject. You can use <code>@{{variableName}}</code> placeholders.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Body (HTML + Blade)</label>
                        <textarea name="body" class="form-control font-monospace" rows="16" style="font-size: .85rem; tab-size: 4;" required>{{ old('body', $body) }}</textarea>
                        <div class="form-text">Uses Blade syntax. Variables are accessed with <code>@{{ $variableName }}</code>. HTML is supported.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" class="icon"></i> Save Template
                        </button>
                        <a href="/{{ $prefix }}/settings/email-templates/{{ $key }}/preview" target="_blank" class="btn btn-outline-secondary">
                            <i data-lucide="eye" class="icon"></i> Preview
                        </a>
                    </div>
                </form>

                @if($customized)
                    <hr class="my-3">
                    <form method="POST" action="/{{ $prefix }}/settings/email-templates/{{ $key }}/reset" onsubmit="return confirm('Reset this template to the default? Your customizations will be lost.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i data-lucide="rotate-ccw" class="icon"></i> Reset to Default
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title"><i data-lucide="code" class="icon"></i> Available Variables</h6>
                <p class="text-muted small">Use these in your template:</p>
                <ul class="list-unstyled mb-0">
                    @foreach($meta['variables'] as $var)
                        <li class="mb-1"><code>@{{ ${{ $var }} }}</code></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6 class="card-title"><i data-lucide="info" class="icon"></i> Tips</h6>
                <ul class="text-muted small mb-0">
                    <li class="mb-1">Use <code>&lt;p&gt;</code> tags for paragraphs</li>
                    <li class="mb-1">Use <code>&lt;strong&gt;</code> for bold text</li>
                    <li class="mb-1">Use <code>&lt;a href="..."&gt;</code> for links</li>
                    <li class="mb-1">Use <code>@@if(!empty($var))</code> for conditional sections</li>
                    <li>The email header and footer are added automatically</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="/{{ $prefix }}/settings/email-templates" class="btn btn-secondary"><i data-lucide="arrow-left" class="icon"></i> Back to Templates</a>
</div>
@endsection
