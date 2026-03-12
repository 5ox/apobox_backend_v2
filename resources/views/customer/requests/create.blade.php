@extends('layouts.default')
@section('title', 'New Request - APO Box')
@section('content')
<h2>New Custom Package Request</h2>
<form method="POST" action="{{ url('/requests/add') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Instructions</label>
        <textarea name="instructions" class="form-control" rows="3" required>{{ old('instructions') }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit Request</button>
    <a href="{{ url('/requests') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
