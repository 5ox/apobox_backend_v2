@extends('layouts.default')
@section('title', 'New Request - APO Box')
@section('content')
<x-page-header title="New Custom Package Request" />

<x-form-section>
    <form method="POST" action="{{ url('/requests/add') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Instructions</label>
            <textarea name="instructions" class="form-control" rows="3" required>{{ old('instructions') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i data-lucide="send" class="icon--sm"></i> Submit Request</button>
        <a href="{{ url('/requests') }}" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
