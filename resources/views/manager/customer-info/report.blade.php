@extends('layouts.manager')
@section('title', 'Customer Info Report - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Customer Info Report" />

<x-form-section title="Report Filters">
    <form method="GET" action="/{{ $prefix }}/customers/report" class="row g-3">
        <div class="col-auto">
            <select name="interval" class="form-select">
                @foreach($validIntervals as $int)
                    <option value="{{ $int }}" @selected($interval == $int)>{{ ucfirst($int) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="sort_field" class="form-select">
                @foreach($validSortFields as $field)
                    <option value="{{ $field }}" @selected(request('sort_field') == $field)>{{ str_replace('customers_info_', '', $field) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
        <div class="col-auto"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
        <div class="col-auto"><button type="submit" class="btn btn-primary"><i data-lucide="play" class="icon-sm me-1"></i>Run</button></div>
    </form>
</x-form-section>

@if($results && $results->isNotEmpty())
    <x-table-card title="Results">
        <div class="table-responsive">
            <table class="table-modern">
                <thead><tr><th>Period</th><th>Count</th></tr></thead>
                <tbody>
                    @foreach($results as $row)
                        <tr><td>{{ $row->period }}</td><td>{{ $row->count }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-table-card>
@endif
@endsection
