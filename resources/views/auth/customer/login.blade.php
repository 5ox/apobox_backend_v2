@extends('layouts.default')
@section('title', 'Sign In - APO Box')
@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="form-signin">
            <div class="text-center mb-4">
                <i data-lucide="package" class="icon--xl text-primary"></i>
            </div>
            <h2 class="text-center mb-3">Please sign in</h2>
            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="mb-3">
                    <input type="email" name="customers_email_address" class="form-control" placeholder="Email Address" required autofocus value="{{ old('customers_email_address') }}">
                </div>
                <div class="mb-3">
                    <input type="password" name="customers_password" class="form-control" placeholder="Password" required>
                </div>
                <button class="btn btn-lg btn-primary w-100" type="submit">Sign in</button>
            </form>
            <p class="text-center mt-3"><a href="{{ url('/forgot-password') }}">Forgot Password?</a></p>
        </div>
    </div>
</div>
@endsection
