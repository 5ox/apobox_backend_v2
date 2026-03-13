@extends('layouts.default')
@section('title', 'Reset Password - APO Box')
@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="form-signin">
            <div class="text-center mb-4">
                <i data-lucide="lock-keyhole" class="icon--xl text-primary"></i>
            </div>
            <h2 class="text-center mb-3">Reset Password</h2>
            <form method="POST" action="{{ url('/reset-password/' . $uuid) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Reset Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
