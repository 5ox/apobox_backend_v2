@extends('layouts.manager')
@section('title', 'Add Scan - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Add Scan</h2>
<form method="POST" action="/{{ $prefix }}/scan">
    @csrf
    <div class="mb-3"><label class="form-label">Tracking Number</label><input type="text" name="tracking_number" class="form-control" value="{{ old('tracking_number') }}" required autofocus></div>
    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
    <button type="submit" class="btn btn-primary">Add Scan</button>
</form>
@endsection
