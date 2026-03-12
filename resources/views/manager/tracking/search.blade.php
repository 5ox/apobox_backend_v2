@extends('layouts.manager')
@section('title', 'Scans - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Scans</h2>
<form method="GET" action="/{{ $prefix }}/scans" class="row g-3 mb-4">
    <div class="col-auto"><input type="text" name="q" class="form-control" placeholder="Search..." value="{{ $search }}"></div>
    <div class="col-auto"><button type="submit" class="btn btn-primary">Search</button></div>
</form>
@if($results->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead><tr><th>Tracking #</th><th>Date</th><th>Notes</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($results as $tracking)
                    <tr>
                        <td>{{ $tracking->tracking_number }}</td>
                        <td>{{ $tracking->timestamp?->format('m/d/Y g:i A') }}</td>
                        <td>{{ $tracking->notes }}</td>
                        <td>
                            <a href="/{{ $prefix }}/scan/edit/{{ $tracking->tracking_id }}">Edit</a>
                            @if($userIsManager) | <a href="/{{ $prefix }}/scan/delete/{{ $tracking->tracking_id }}" onclick="return confirm('Delete?')">Delete</a>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $results->appends(request()->query())->links() }}
@endif
@endsection
