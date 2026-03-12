@extends('layouts.default')
@section('title', 'Reset Password - APO Box')
@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <h2 class="mb-3">Reset Password</h2>
        <form method="POST" action="{{ url('/reset-password/' . $uuid) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button class="btn btn-primary" type="submit">Reset Password</button>
        </form>
    </div>
</div>
@endsection
