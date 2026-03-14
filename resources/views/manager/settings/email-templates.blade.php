@extends('layouts.manager')
@section('title', 'Email Templates - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Email Templates" subtitle="View and edit email templates sent to customers" />

<div class="card">
    <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th>Template</th>
                    <th>Description</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $t)
                    <tr>
                        <td><strong>{{ $t['label'] }}</strong></td>
                        <td class="text-muted small">{{ $t['description'] }}</td>
                        <td class="small font-monospace">{{ $t['default_subject'] }}</td>
                        <td>
                            @if($t['customized'])
                                <span class="badge bg-primary-subtle text-primary">Customized</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Default</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="/{{ $prefix }}/settings/email-templates/{{ $t['key'] }}/edit" class="btn btn-sm btn-outline-primary">
                                <i data-lucide="pencil" class="icon"></i> Edit
                            </a>
                            <a href="/{{ $prefix }}/settings/email-templates/{{ $t['key'] }}/preview" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i data-lucide="eye" class="icon"></i> Preview
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    <a href="/{{ $prefix }}/settings" class="btn btn-secondary"><i data-lucide="arrow-left" class="icon"></i> Back to Settings</a>
</div>
@endsection
