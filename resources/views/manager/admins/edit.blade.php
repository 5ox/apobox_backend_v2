@extends('layouts.manager')
@section('title', 'Edit Admin - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Edit Admin" />

<x-form-section title="Edit Admin">
    <form method="POST" action="/{{ $prefix }}/admins/edit/{{ $admin->id }}">
        @csrf
        @if($errors->any())<div class="alert alert-danger mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required></div>
        <div class="mb-3"><label class="form-label">Password (leave blank to keep current)</label><input type="password" name="password" class="form-control"></div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="employee" @selected($admin->role === 'employee')>Employee</option>
                <option value="manager" @selected($admin->role === 'manager')>Manager</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="/{{ $prefix }}/admins/index" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
