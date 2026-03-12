@extends('layouts.manager')
@section('title', 'Edit Request - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Edit Request" />

<x-form-section title="Request Details">
    <form method="POST" action="/{{ $prefix }}/requests/edit/{{ $packageRequest->custom_orders_id }}">
        @csrf
        <div class="mb-3"><label class="form-label">Customer</label><p class="form-control-plaintext">{{ $packageRequest->customer?->full_name }}</p></div>
        <div class="mb-3"><label class="form-label">Instructions</label><textarea name="instructions" class="form-control" rows="3">{{ old('instructions', $packageRequest->instructions) }}</textarea></div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="package_status" class="form-select">
                @foreach($packageStatuses as $id => $name)
                    <option value="{{ $id }}" @selected($packageRequest->package_status == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i data-lucide="save" class="icon-sm me-1"></i>Save</button>
        <a href="/{{ $prefix }}/requests" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
