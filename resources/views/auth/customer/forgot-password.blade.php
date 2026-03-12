@extends('layouts.default')
@section('title', 'Forgot Password - APO Box')
@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <h2 class="mb-3">Forgot Password</h2>
        <p>Enter your email address and we'll send you a link to reset your password.</p>
        <form method="POST" action="{{ url('/forgot-password') }}">
            @csrf
            <div class="mb-3">
                <input type="email" name="customers_email_address" class="form-control" placeholder="Email Address" required autofocus value="{{ old('customers_email_address') }}">
            </div>
            <button class="btn btn-primary" type="submit">Send Reset Link</button>
        </form>
        <p class="mt-3"><a href="{{ url('/login') }}">Back to Login</a></p>
    </div>
</div>
@endsection
