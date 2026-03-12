@extends('layouts.default')
@section('title', 'My Requests - APO Box')
@section('content')
<h2>My Custom Package Requests <small><a href="{{ url('/requests/add') }}" class="btn btn-sm btn-primary">New Request</a></small></h2>
@if($requests->isEmpty())
    <p>You have no custom package requests.</p>
@else
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead><tr><th>Date</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($requests as $request)
                    <tr>
                        <td>{{ $request->request_date?->format('m/d/Y') }}</td>
                        <td>{{ $request->description }}</td>
                        <td>{{ $request->status_label ?? $request->status }}</td>
                        <td>
                            <a href="{{ url('/requests/edit/' . $request->custom_package_requests_id) }}">Edit</a> |
                            <a href="{{ url('/requests/delete/' . $request->custom_package_requests_id) }}" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $requests->links() }}
@endif
@endsection
