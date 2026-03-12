@php
    $prefix = auth('admin')->user()?->role === 'manager' ? 'manager' : 'employee';
    $isManager = auth('admin')->user()?->role === 'manager';
    $orderStatuses = $orderStatuses ?? \App\Models\OrderStatus::all();
@endphp
<aside class="admin-sidebar d-none d-md-block">
    <div class="sidebar-section">
        <div class="sidebar-section-title">Navigation</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="/{{ $prefix }}/customers"><i data-lucide="users" class="icon"></i> All Customers</a>
            <a class="nav-link" href="/{{ $prefix }}/orders"><i data-lucide="package" class="icon"></i> All Orders</a>
            <a class="nav-link" href="/{{ $prefix }}/requests"><i data-lucide="file-text" class="icon"></i> Custom Requests</a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Filter by Status</div>
        <div class="d-flex flex-wrap gap-1 px-3">
            @foreach($orderStatuses as $status)
                <a href="/{{ $prefix }}/orders?showStatus={{ $status->orders_status_id }}"
                   class="status-badge status-badge--{{ \Illuminate\Support\Str::slug($status->orders_status_name) }} status-badge--filter text-decoration-none">
                    {{ $status->orders_status_name }}
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
            </nav>
        </div>
    @endif
</aside>
