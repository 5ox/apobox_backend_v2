@extends('layouts.manager')
@section('title', 'Scans - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Scans" />

<x-form-section>
    <form method="GET" action="/{{ $prefix }}/scans" class="row g-3">
        <div class="col-auto"><input type="text" name="q" class="form-control" placeholder="Search..." value="{{ $search }}"></div>
        <div class="col-auto"><button type="submit" class="btn btn-primary"><i data-lucide="search" class="icon-sm me-1"></i>Search</button></div>
    </form>
</x-form-section>

@if($results->isNotEmpty())
    <x-table-card>
        <div class="table-responsive">
            <table class="table-modern">
                <thead><tr><th>Tracking #</th><th>Date</th><th>Notes</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($results as $tracking)
                        <tr>
                            <td>{{ $tracking->tracking_number }}</td>
                            <td>{{ $tracking->timestamp?->format('m/d/Y g:i A') }}</td>
                            <td>{{ $tracking->notes }}</td>
                            <td>
                                <a href="/{{ $prefix }}/scan/edit/{{ $tracking->tracking_id }}" class="btn btn-sm btn-outline-primary"><i data-lucide="pencil" class="icon-sm"></i></a>
                                @if($userIsManager)
                                    <a href="/{{ $prefix }}/scan/delete/{{ $tracking->tracking_id }}" onclick="return confirm('Delete?')" class="btn btn-sm btn-outline-danger"><i data-lucide="trash-2" class="icon-sm"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-slot:footer>
            {{ $results->appends(request()->query())->links() }}
        </x-slot:footer>
    </x-table-card>
@endif
@endsection
