@extends('layouts.manager')
@section('title', 'Reports - APO Box Admin')
@section('module', 'reports')
@section('content')

<x-page-header title="Reports">
    <x-slot:actions>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary" data-range="7d">7D</button>
            <button type="button" class="btn btn-outline-primary active" data-range="30d">30D</button>
            <button type="button" class="btn btn-outline-primary" data-range="90d">90D</button>
            <button type="button" class="btn btn-outline-primary" data-range="12m">12M</button>
        </div>
    </x-slot:actions>
</x-page-header>

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
</div>

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
    <div class="col-lg-4">
        <x-detail-card title="Package Size Distribution">
            <div style="height: 280px; position: relative;">
                <canvas id="sizeDonutChart"></canvas>
            </div>
            <p class="text-muted small mt-2 mb-0 text-center">Click a segment to filter the table below</p>
        </x-detail-card>
    </div>
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
</div>

{{-- ============================================================ --}}
{{-- Status Overview --}}
{{-- ============================================================ --}}
<div class="row mb-4">
    <div class="col-md-6">
        <x-detail-card title="Order Status Overview">
            <div id="status-counts" data-statuses='@json($statuses)'></div>
        </x-detail-card>
    </div>
</div>

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

@endsection
