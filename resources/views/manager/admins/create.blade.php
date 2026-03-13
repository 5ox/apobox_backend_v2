@extends('layouts.manager')
@section('title', 'Add Admin - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Add Admin" />

<x-form-section title="Create Admin">
    <form method="POST" action="/{{ $prefix }}/admins/add">
        @csrf
        @if($errors->any())<div class="alert alert-danger mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="employee">Employee</option>
                <option value="manager">Manager</option>
                @if(auth('admin')->user()->isSysadmin())
                    <option value="sysadmin">Sys Admin</option>
                @endif
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="/{{ $prefix }}/admins/index" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
