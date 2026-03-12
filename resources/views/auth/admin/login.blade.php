@extends('layouts.default')
@section('title', 'Admin Login - APO Box')
@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <form method="POST" action="{{ url('/admin/login') }}">
            @csrf
            <h2 class="mb-3">Admin Sign In</h2>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required autofocus value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button class="btn btn-lg btn-primary w-100" type="submit">Sign in</button>
        </form>
        <hr>
        <a href="{{ url('/admin/login-google') }}" class="btn btn-outline-secondary w-100">
            <i class="fa fa-google"></i> Sign in with Google
        </a>
    </div>
</div>
@endsection
