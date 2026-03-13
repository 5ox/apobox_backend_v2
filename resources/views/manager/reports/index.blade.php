@extends('layouts.manager')
@section('title', 'Reports - APO Box Admin')
@section('module', 'reports')
@section('content')
@php $isManager = auth('admin')->user()?->role === 'manager'; @endphp

<x-page-header title="Reports">
    @if($isManager)
        <x-slot:actions>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary" data-range="7d">7D</button>
                <button type="button" class="btn btn-outline-primary active" data-range="30d">30D</button>
                <button type="button" class="btn btn-outline-primary" data-range="90d">90D</button>
                <button type="button" class="btn btn-outline-primary" data-range="12m">12M</button>
            </div>
        </x-slot:actions>
    @endif
</x-page-header>

@if($isManager)
{{-- ============================================================ --}}
{{-- KPI Cards --}}
{{-- ============================================================ --}}
<div class="dashboard-stats mb-4">
    <div class="card card-hover kpi-card">
        <div class="stat-card">
            <div class="stat-card__icon"><i data-lucide="package" class="icon--lg"></i></div>
            <div class="stat-card__number" id="kpi-total-orders">--</div>
            <div class="stat-card__label">Packages This Period</div>
            <div class="mt-1"><span id="kpi-percent-change" class="badge bg-secondary-subtle text-secondary">--</span></div>
        </div>
    </div>
    <div class="card card-hover kpi-card">
        <div class="stat-card">
            <div class="stat-card__icon"><i data-lucide="users" class="icon--lg"></i></div>
            <div class="stat-card__number" id="kpi-active-customers">--</div>
            <div class="stat-card__label">Active Customers</div>
        </div>
    </div>
    <div class="card card-hover kpi-card">
        <div class="stat-card">
            <div class="stat-card__icon"><i data-lucide="bar-chart-3" class="icon--lg"></i></div>
            <div class="stat-card__number" id="kpi-avg-per-customer">--</div>
            <div class="stat-card__label">Avg Packages / Customer</div>
        </div>
    </div>
    <div class="card card-hover kpi-card">
        <div class="stat-card">
            <div class="stat-card__icon"><i data-lucide="user-plus" class="icon--lg"></i></div>
            <div class="stat-card__number" id="kpi-total-customers">--</div>
            <div class="stat-card__label">Total Customers</div>
        </div>
    </div>
    <div class="card card-hover kpi-card">
        <div class="stat-card">
            <div class="stat-card__icon"><i data-lucide="package" class="icon--lg"></i></div>
            <div class="stat-card__number" id="kpi-lifetime-shipped">--</div>
            <div class="stat-card__label">Lifetime Orders</div>
        </div>
    </div>
</div>
@endif

{{-- ============================================================ --}}
{{-- Section A: Package Volume Trends --}}
{{-- ============================================================ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="detail-card">
            <div class="detail-card__header">
                <h5>Package Volume Trends</h5>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control form-control-sm" id="trends-from"
                               value="{{ now()->subYear()->format('Y-m-d') }}">
                        <input type="date" class="form-control form-control-sm" id="trends-to"
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" data-interval="day">Day</button>
                        <button type="button" class="btn btn-outline-secondary active" data-interval="week">Week</button>
                        <button type="button" class="btn btn-outline-secondary" data-interval="month">Month</button>
                    </div>
                </div>
            </div>
            <div class="detail-card__body">
                <div style="height: 320px; position: relative;">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- Section B + C: Size Distribution / Customer Growth / Top Customers --}}
{{-- ============================================================ --}}
<div class="row mb-4">
    <div class="{{ $isManager ? 'col-lg-4' : 'col-lg-6' }}">
        <x-detail-card title="Package Size Distribution">
            <div style="height: 280px; position: relative;">
                <canvas id="sizeDonutChart"></canvas>
            </div>
            @if($isManager)
                <p class="text-muted small mt-2 mb-0 text-center">Click a segment to filter the table below</p>
            @endif
        </x-detail-card>
    </div>
    @if($isManager)
        <div class="col-lg-4">
            <x-detail-card title="Customer Growth">
                <div style="height: 280px; position: relative;">
                    <canvas id="customerGrowthChart"></canvas>
                </div>
            </x-detail-card>
        </div>
        <div class="col-lg-4">
            <x-detail-card title="Top 10 Customers (This Period)">
                <div style="height: 280px; position: relative;">
                    <canvas id="topCustomersChart"></canvas>
                </div>
            </x-detail-card>
        </div>
    @endif
</div>

{{-- ============================================================ --}}
{{-- Average Weight Trends + Top Destination Zip Codes --}}
{{-- ============================================================ --}}
<div class="row mb-4">
    <div class="col-lg-6">
        <x-detail-card title="Average Weight Shipped (lbs)">
            <div style="height: 280px; position: relative;">
                <canvas id="avgWeightChart"></canvas>
            </div>
        </x-detail-card>
    </div>
    <div class="col-lg-6">
        <x-detail-card title="Top Destination Zip Codes">
            <div style="height: 280px; position: relative;">
                <canvas id="destinationsChart"></canvas>
            </div>
        </x-detail-card>
    </div>
</div>

{{-- ============================================================ --}}
{{-- Status Overview + Employee Activity --}}
{{-- ============================================================ --}}
<div class="row mb-4">
    <div class="col-md-6">
        <x-detail-card title="Order Status Overview">
            <div id="status-counts" data-statuses='@json($statuses)'></div>
        </x-detail-card>
    </div>
    <div class="col-md-6">
        <x-detail-card title="Employee Activity (30 Days)">
            @if($employeeTotals->isNotEmpty())
                {{-- Summary cards --}}
                <div class="d-flex flex-wrap gap-3 mb-3">
                    @foreach($employeeTotals as $id => $total)
                        @php $isTop = $loop->first && $employeeTotals->count() > 1; @endphp
                        <div class="d-flex align-items-center gap-2 {{ $isTop ? 'border rounded px-3 py-2 bg-warning bg-opacity-10' : 'px-2 py-1' }}">
                            @if($isTop)
                                <i data-lucide="trophy" class="text-warning" style="width:16px;height:16px"></i>
                            @else
                                <i data-lucide="user" class="text-muted" style="width:16px;height:16px"></i>
                            @endif
                            <span class="{{ $isTop ? 'fw-semibold' : '' }}">{{ $employeeNames[$id] }}</span>
                            <span class="badge {{ $isTop ? 'bg-warning text-dark' : 'bg-secondary' }} rounded-pill">{{ $total }}</span>
                        </div>
                    @endforeach
                </div>
                {{-- Daily breakdown table --}}
                <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="table table-sm table-borderless mb-0 small align-middle">
                        <thead class="sticky-top bg-white">
                            <tr class="text-muted">
                                <th class="fw-semibold">Day</th>
                                @foreach($employeeNames as $name)
                                    <th class="text-center fw-semibold">{{ $name }}</th>
                                @endforeach
                                <th class="text-center fw-semibold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employeeActivity->reverse() as $day)
                                <tr @if($day['date']->isToday()) class="table-active fw-semibold" @endif>
                                    <td class="text-nowrap">{{ $day['label'] }}</td>
                                    @foreach($employeeNames as $id => $name)
                                        @php $cnt = $day['byEmployee'][$id] ?? 0; @endphp
                                        <td class="text-center">
                                            @if($cnt > 0)
                                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">{{ $cnt }}</span>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        @if($day['total'] > 0)
                                            <span class="badge bg-dark rounded-pill">{{ $day['total'] }}</span>
                                        @else
                                            <span class="text-muted">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">No employee activity in the last 30 days</p>
            @endif
        </x-detail-card>
    </div>
</div>

@if($isManager)
{{-- ============================================================ --}}
{{-- Section D: Sortable, Filterable Orders Table --}}
{{-- ============================================================ --}}
<div id="orders-table-section">
    <div class="table-card">
        <div class="table-card__header">
            <h5>Orders</h5>
            <div class="d-flex align-items-center gap-2">
                <span id="orders-count" class="text-muted small"></span>
                <button type="button" class="btn btn-sm btn-outline-success" id="btn-export-csv">
                    <i data-lucide="download" class="icon--sm"></i> Export CSV
                </button>
            </div>
        </div>

        {{-- Filter bar --}}
        <div class="p-3 border-bottom" style="background: var(--apo-surface-hover);">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small mb-1">Search</label>
                    <input type="text" class="form-control form-control-sm" id="table-search"
                           placeholder="Customer or tracking #...">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="table-from">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="table-to">
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small mb-1">Status</label>
                    <select class="form-select form-select-sm" id="table-status">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small mb-1">Package Type</label>
                    <select class="form-select form-select-sm" id="table-package-type">
                        <option value="">All Types</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btn-clear-filters">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="sortable-header sort-desc" data-sort="date_purchased">Date</th>
                        <th class="sortable-header" data-sort="customers_name">Customer</th>
                        <th>Tracking #</th>
                        <th class="sortable-header" data-sort="package_type">Size</th>
                        <th class="sortable-header" data-sort="weight_oz">Weight</th>
                        <th class="sortable-header" data-sort="orders_status">Status</th>
                        <th>Destination</th>
                        <th style="width: 60px;"></th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="table-card__footer d-flex justify-content-between align-items-center">
            <div id="orders-pagination"></div>
        </div>
    </div>
</div>
@endif

@endsection
