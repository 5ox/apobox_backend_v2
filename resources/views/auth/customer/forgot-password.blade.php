@extends('layouts.default')
@section('title', 'Forgot Password - APO Box')
@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="form-signin">
            <div class="text-center mb-4">
                <i data-lucide="key-round" class="icon--xl text-primary"></i>
            </div>
            <h2 class="text-center mb-3">Forgot Password</h2>
            <p class="text-center text-muted">Enter your email address and we'll send you a link to reset your password.</p>
            <form method="POST" action="{{ url('/forgot-password') }}">
                @csrf
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required autofocus value="{{ old('email') }}">
                </div>
                <button class="btn btn-primary w-100" type="submit">Send Reset Link</button>
            </form>
            <p class="text-center mt-3"><a href="{{ url('/login') }}">Back to Login</a></p>
        </div>
    </div>
</div>
@endsection
