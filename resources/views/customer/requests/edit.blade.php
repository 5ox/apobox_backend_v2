@extends('layouts.default')
@section('title', 'Edit Request - APO Box')
@section('content')
<h2>Edit Custom Package Request</h2>
<form method="POST" action="{{ url('/requests/edit/' . $packageRequest->custom_orders_id) }}">
    @csrf
    @if(in_array('instructions', $allowedFields))
        <div class="mb-3">
            <label class="form-label">Instructions</label>
            <textarea name="instructions" class="form-control" rows="3">{{ old('instructions', $packageRequest->instructions) }}</textarea>
        </div>
    @endif
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ url('/requests') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
