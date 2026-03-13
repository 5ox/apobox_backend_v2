@extends('layouts.manager')
@section('title', 'Manage Admins - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Manage Admins">
    <a href="/{{ $prefix }}/admins/add" class="btn btn-sm btn-primary"><i data-lucide="plus"></i> Add Admin</a>
</x-page-header>

<x-table-card title="Admins">
    <table class="table table-modern">
        <thead><tr><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($admins as $admin)
                <tr>
                    <td>{{ $admin->email }}</td>
                    <td>{{ ucfirst($admin->role) }}</td>
                    <td>
                        <a href="/{{ $prefix }}/admins/edit/{{ $admin->id }}">Edit</a> |
                        <a href="/{{ $prefix }}/admins/delete/{{ $admin->id }}" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-table-card>

{{ $admins->links() }}
@endsection
