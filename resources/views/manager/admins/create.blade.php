@extends('layouts.manager')
@section('title', 'Add Admin - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Add Admin</h2>
<form method="POST" action="/{{ $prefix }}/admins/add">
    @csrf
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
    <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
            <option value="employee">Employee</option>
            <option value="manager">Manager</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
    <a href="/{{ $prefix }}/admins/index" class="btn btn-secondary">Cancel</a>
</form>
@endsection
