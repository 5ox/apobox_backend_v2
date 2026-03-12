@php
    $prefix = auth('admin')->user()?->role === 'manager' ? 'manager' : 'employee';
    $isManager = auth('admin')->user()?->role === 'manager';
    $orderStatuses = $orderStatuses ?? \App\Models\OrderStatus::all();
@endphp
<nav class="col-md-2 d-none d-md-block bg-light sidebar py-3">
    <div class="sidebar-sticky">
        <p><a href="/{{ $prefix }}/customers">All Customers</a></p>
        <p>
            <a href="/{{ $prefix }}/orders">All Orders</a>
            @foreach($orderStatuses as $status)
                <a href="/{{ $prefix }}/orders?showStatus={{ $status->orders_status_id }}" class="badge bg-secondary text-decoration-none">{{ $status->orders_status_name }}</a>
            @endforeach
        </p>
        <p><a href="/{{ $prefix }}/requests">Custom Package Requests</a></p>
        <p>
            <a href="/{{ $prefix }}/scan">Add Scan</a> |
            <a href="/{{ $prefix }}/scans">View Scans</a>
        </p>
        @if($isManager)
            <p><a href="/{{ $prefix }}/admins/index">Manage Admins</a></p>
        @endif
    </div>
</nav>
