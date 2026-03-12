@extends('layouts.default')
@section('title', 'Change Password - APO Box')
@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <h2>Change Password</h2>
        <form method="POST" action="{{ url('/customers/change-password') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
            <a href="{{ url('/account') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
