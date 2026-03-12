@extends('layouts.manager')
@section('title', 'Manage Admins - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Admins <a href="/{{ $prefix }}/admins/add" class="btn btn-sm btn-primary">Add Admin</a></h2>
<table class="table table-sm table-striped">
    <thead><tr><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
    <tbody>
        @foreach($admins as $admin)
            <tr>
                <td>{{ $admin->email }}</td>
                <td>{{ ucfirst($admin->role) }}</td>
                <td>
                    <a href="/{{ $prefix }}/admins/edit/{{ $admin->admin_id }}">Edit</a> |
                    <a href="/{{ $prefix }}/admins/delete/{{ $admin->admin_id }}" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
{{ $admins->links() }}
@endsection
