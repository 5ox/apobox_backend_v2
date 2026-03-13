@php
    $prefix = auth('admin')->user()?->role === 'manager' ? 'manager' : 'employee';
    $isManager = auth('admin')->user()?->role === 'manager';
    $orderStatuses = $orderStatuses ?? \App\Models\OrderStatus::all();

    // Shipped count for last 24 hours (cached 5 min)
    $shippedCount24h = \Illuminate\Support\Facades\Cache::remember('sidebar:shipped_24h', 300, function () {
        return \App\Models\Order::where('orders_status', 3)
            ->where('last_modified', '>=', now()->subDay())
            ->count();
    });

    // All-time status counts (cached 5 min)
    $statusCountsAll = \Illuminate\Support\Facades\Cache::remember('sidebar:status_counts_all', 300, function () {
        return \App\Models\Order::select('orders_status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->groupBy('orders_status')
            ->pluck('count', 'orders_status');
    });
@endphp
<aside class="admin-sidebar d-none d-md-block">
    <div class="sidebar-section">
        <div class="sidebar-section-title">Quick Actions</div>
        <nav class="nav flex-column">
            <a class="nav-link sidebar-link--primary" href="/{{ $prefix }}/orders/new"><i data-lucide="plus-circle" class="icon"></i> New Order</a>
        </nav>
    </div>

    <div class="sidebar-section">
    <div class="sidebar-section-title">Navigation</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="/{{ $prefix }}/customers"><i data-lucide="users" class="icon"></i> All Customers</a>
            <a class="nav-link" href="/{{ $prefix }}/orders"><i data-lucide="package" class="icon"></i> All Orders</a>
            <a class="nav-link" href="/{{ $prefix }}/requests"><i data-lucide="file-text" class="icon"></i> Custom Requests</a>
            <a class="nav-link" href="/{{ $prefix }}/reports/index"><i data-lucide="bar-chart-3" class="icon"></i> Reports</a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Last 24 Hours</div>
        <div class="px-3">
            <a href="/{{ $prefix }}/orders?showStatus=3"
               class="d-flex justify-content-between align-items-center py-1 text-decoration-none">
                <span class="status-badge status-badge--shipped status-badge--filter">Shipped</span>
                <span class="badge {{ $shippedCount24h > 0 ? 'bg-dark' : 'bg-secondary bg-opacity-25 text-muted' }} rounded-pill">{{ $shippedCount24h }}</span>
            </a>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Status</div>
        <div class="px-3">
            @foreach($orderStatuses as $status)
                @php $count = $statusCountsAll[$status->orders_status_id] ?? 0; @endphp
                <a href="/{{ $prefix }}/orders?showStatus={{ $status->orders_status_id }}"
                   class="d-flex justify-content-between align-items-center py-1 text-decoration-none">
                    <span class="status-badge status-badge--{{ \Illuminate\Support\Str::slug($status->orders_status_name) }} status-badge--filter">
                        {{ $status->orders_status_name }}
                    </span>
                    <span class="badge {{ $count > 0 ? 'bg-dark' : 'bg-secondary bg-opacity-25 text-muted' }} rounded-pill">{{ $count }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Scanning</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="/{{ $prefix }}/scan"><i data-lucide="scan-line" class="icon"></i> Add Scan</a>
            <a class="nav-link" href="/{{ $prefix }}/scans"><i data-lucide="list" class="icon"></i> View Scans</a>
        </nav>
    </div>

    @if($isManager)
        <div class="sidebar-section">
            <div class="sidebar-section-title">Management</div>
            <nav class="nav flex-column">
                <a class="nav-link" href="/{{ $prefix }}/admins/index"><i data-lucide="shield" class="icon"></i> Manage Admins</a>
                <a class="nav-link" href="/{{ $prefix }}/tools"><i data-lucide="wrench" class="icon"></i> Tools</a>
            </nav>
        </div>
    @endif
</aside>
