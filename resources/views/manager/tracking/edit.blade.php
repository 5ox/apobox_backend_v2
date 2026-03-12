@extends('layouts.manager')
@section('title', 'Edit Scan - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Edit Scan" />

<x-form-section title="Scan Details">
    <form method="POST" action="/{{ $prefix }}/scan/edit/{{ $tracking->tracking_id }}">
        @csrf
        <div class="mb-3"><label class="form-label">Tracking Number</label><input type="text" name="tracking_number" class="form-control" value="{{ old('tracking_number', $tracking->tracking_number) }}" required></div>
        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $tracking->notes) }}</textarea></div>
        <button type="submit" class="btn btn-primary"><i data-lucide="save" class="icon-sm me-1"></i>Save</button>
        <a href="/{{ $prefix }}/scans" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
