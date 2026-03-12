@extends('layouts.manager')
@section('title', 'Demographics Report - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Demographics Report" />

<x-form-section title="Report Filters">
    <form method="GET" action="/{{ $prefix }}/customers/demographics" class="row g-3">
        <div class="col-auto">
            <select name="field" class="form-select">
                @foreach($reportFields as $key => $label)
                    <option value="{{ $key }}" @selected($options['field'] == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><input type="number" name="limit" class="form-control" placeholder="Limit" value="{{ $options['limit'] }}"></div>
        <div class="col-auto"><input type="date" name="from_date" class="form-control" value="{{ $options['from_date'] }}"></div>
        <div class="col-auto"><input type="date" name="to_date" class="form-control" value="{{ $options['to_date'] }}"></div>
        <div class="col-auto"><button type="submit" class="btn btn-primary">Run Report</button></div>
    </form>
</x-form-section>

@if($data->isNotEmpty())
    <x-table-card title="Results">
        <table class="table table-modern">
            <thead><tr><th>{{ $reportFields[$options['field']] ?? 'Label' }}</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($data as $row)
                    <tr><td>{{ $row->label }}</td><td>{{ $row->total }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </x-table-card>
@endif
@endsection
