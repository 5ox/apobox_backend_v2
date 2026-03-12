@extends('layouts.manager')
@section('title', 'Affiliate Links - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Affiliate Links <a href="/{{ $prefix }}/affiliate-links/add" class="btn btn-sm btn-primary">Add</a></h2>
<table class="table table-sm table-striped">
    <thead><tr><th>Name</th><th>Code</th><th>URL</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
        @foreach($affiliateLinks as $link)
            <tr>
                <td>{{ $link->name }}</td>
                <td>{{ $link->code }}</td>
                <td>{{ $link->url }}</td>
                <td>{{ $link->created?->format('m/d/Y') }}</td>
                <td>
                    <a href="/{{ $prefix }}/affiliate-links/edit/{{ $link->affiliate_link_id }}">Edit</a> |
                    <a href="/{{ $prefix }}/affiliate-links/delete/{{ $link->affiliate_link_id }}" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
{{ $affiliateLinks->links() }}
@endsection
