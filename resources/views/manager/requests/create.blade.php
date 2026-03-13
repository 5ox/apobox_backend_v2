@extends('layouts.manager')
@section('title', 'New Request - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="New Request for {{ $customer->full_name }}" />

<x-form-section title="Request Details">
    <form method="POST" action="/{{ $prefix }}/customer/{{ $customer->customers_id }}/request/add">
        @csrf
        <div class="mb-3"><label class="form-label">Instructions</label><textarea name="instructions" class="form-control" rows="3" required>{{ old('instructions') }}</textarea></div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="package_status" class="form-select">
                @foreach($packageStatuses as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i data-lucide="plus" class="icon-sm me-1"></i>Create</button>
        <a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
