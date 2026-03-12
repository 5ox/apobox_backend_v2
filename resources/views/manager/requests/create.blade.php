@extends('layouts.manager')
@section('title', 'New Request - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>New Request for {{ $customer->full_name }}</h2>
<form method="POST" action="/{{ $prefix }}/customer/{{ $customer->customers_id }}/request/add">
    @csrf
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" required>{{ old('description') }}</textarea></div>
    <div class="mb-3"><label class="form-label">Instructions</label><textarea name="instructions" class="form-control" rows="3">{{ old('instructions') }}</textarea></div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach($packageStatuses as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
    <a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
