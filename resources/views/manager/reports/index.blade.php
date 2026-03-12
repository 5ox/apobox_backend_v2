@extends('layouts.manager')
@section('title', 'Reports - APO Box Admin')
@section('content')

<x-page-header title="Reports" />

<div class="row">
    <div class="col-md-6">
        <x-detail-card title="Sales (Last 7 Months)">
            <canvas id="salesChart" height="200"></canvas>
        </x-detail-card>
    </div>
    <div class="col-md-6">
        <x-detail-card title="Signups (Last 7 Months)">
            <canvas id="signupChart" height="200"></canvas>
        </x-detail-card>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <x-table-card title="Order Status Counts">
            <table class="table-modern">
                <tbody>
                    @foreach($statusCounts as $statusId => $count)
                        <tr><td>Status {{ $statusId }}</td><td>{{ $count }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </x-table-card>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: @json($salesChartData->pluck('period')),
            datasets: [
                { label: 'Total', data: @json($salesChartData->pluck('count')), backgroundColor: 'rgba(54, 162, 235, 0.5)' },
                { label: 'Active', data: @json($salesChartData->pluck('active_count')), backgroundColor: 'rgba(75, 192, 192, 0.5)' }
            ]
        }
    });
    new Chart(document.getElementById('signupChart'), {
        type: 'bar',
        data: {
            labels: @json($signupChartData->pluck('period')),
            datasets: [{ label: 'Signups', data: @json($signupChartData->pluck('count')), backgroundColor: 'rgba(153, 102, 255, 0.5)' }]
        }
    });
</script>
@endpush
