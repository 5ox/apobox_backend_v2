@extends('layouts.manager')
@section('title', 'Edit Request - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Edit Request</h2>
<form method="POST" action="/{{ $prefix }}/requests/edit/{{ $packageRequest->custom_package_requests_id }}">
    @csrf
    <div class="mb-3"><label class="form-label">Customer</label><p class="form-control-plaintext">{{ $packageRequest->customer?->full_name }}</p></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $packageRequest->description) }}</textarea></div>
    <div class="mb-3"><label class="form-label">Instructions</label><textarea name="instructions" class="form-control" rows="3">{{ old('instructions', $packageRequest->instructions) }}</textarea></div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach($packageStatuses as $id => $name)
                <option value="{{ $id }}" @selected($packageRequest->status == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="/{{ $prefix }}/requests" class="btn btn-secondary">Cancel</a>
</form>
@endsection
