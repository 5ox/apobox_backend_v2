@extends('layouts.manager')
@section('title', 'Dashboard - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

{{-- Quick Order Add --}}
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a6cf7 100%);">
    <div class="card-body py-3">
        <form action="{{ route($prefix . '.customers.quick-order') }}" method="GET" class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2 text-white flex-shrink-0">
                <i data-lucide="plus-circle" style="width:22px;height:22px"></i>
                <span class="fw-semibold">Quick Order</span>
            </div>
            <div class="input-group">
                <input type="text" name="q" class="form-control form-control-lg" placeholder="Scan or type Billing ID..." autofocus>
                <button type="submit" class="btn btn-light fw-semibold px-4">ADD ORDER</button>
            </div>
        </form>
    </div>
</div>

{{-- Search --}}
<div class="mb-4">
    <form action="/{{ $prefix }}/dashboard" method="GET" class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search by Order #, Customer, Billing ID, or Tracking #">
        <button type="submit" class="btn btn-outline-primary fw-semibold px-4">SEARCH</button>
    </form>
</div>

{{-- Employee Activity --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <i data-lucide="package" class="text-primary" style="width:18px;height:18px"></i>
                <span class="fw-semibold">Employee Activity</span>
                <span class="badge bg-primary rounded-pill">{{ $statsTotal }}</span>
            </div>
            <div class="vr d-none d-sm-block"></div>
            {{-- Per-employee totals --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($employeeTotals->isNotEmpty())
                    @foreach($employeeTotals as $id => $total)
                        @php $isTop = $loop->first && $employeeTotals->count() > 1; @endphp
                        <span class="small {{ $isTop ? 'fw-semibold' : '' }}">
                            @if($isTop)<i data-lucide="trophy" class="text-warning" style="width:14px;height:14px;vertical-align:-2px"></i>@endif
                            {{ $employeeNames[$id] }}
                            <span class="badge {{ $isTop ? 'bg-warning text-dark' : 'bg-secondary' }} rounded-pill">{{ $total }}</span>
                        </span>
                    @endforeach
                @else
                    <span class="text-muted small">No packages yet</span>
                @endif
            </div>
            <div class="ms-auto">
                <div class="btn-group btn-group-sm" role="group">
                    @foreach(['7d' => '7D', '30d' => '30D', '90d' => '90D', '12m' => '12M'] as $val => $label)
                        <a href="?stats={{ $val }}" class="btn btn-outline-primary btn-sm {{ $statsRange === $val ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@foreach([
    ['label' => 'Paid', 'orders' => $paid, 'status' => 'paid'],
    ['label' => 'Warehouse', 'orders' => $inWarehouse, 'status' => 'warehouse'],
    ['label' => 'Problem', 'orders' => $problem, 'status' => 'problem'],
] as $section)
    @if($section['orders']->isNotEmpty())
    <div class="mb-4">
        <h2 class="mb-3 d-flex align-items-center gap-2">
            <x-status-badge :status="$section['label']" class="fs-6 px-3 py-2" />
            <span class="badge bg-secondary rounded-pill fs-6">{{ $section['orders']->total() }}</span>
        </h2>
        <div class="table-responsive">
            <table class="table table-modern table-sm align-middle">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>To</th>
                        <th>Dimensions</th>
                        <th>Weight</th>
                        <th>Class</th>
                        <th>Inbound Tracking</th>
                        <th title="Comments"><i data-lucide="message-square" class="icon--sm"></i></th>
                        <th>Modified</th>
                        <th>Processed</th>
                        <th class="text-end">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($section['orders'] as $order)
                    @php
                        $dims = collect([$order->length, $order->width, $order->depth])
                            ->filter()
                            ->map(fn($v) => rtrim(rtrim(number_format((float)$v, 2), '0'), '.'));
                        $lbs = intdiv((int)($order->weight_oz ?? 0), 16);
                        $oz = (int)($order->weight_oz ?? 0) % 16;
                        // Determine inbound tracking number and carrier
                        $inboundTrack = '';
                        $inboundCarrier = '';
                        $inboundUrl = '';
                        if ($order->usps_track_num_in) {
                            $inboundTrack = $order->usps_track_num_in;
                            $inboundCarrier = 'USPS';
                            $inboundUrl = 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . $inboundTrack;
                        } elseif ($order->ups_track_num) {
                            $inboundTrack = $order->ups_track_num;
                            $inboundCarrier = 'UPS';
                            $inboundUrl = 'https://www.ups.com/track?tracknum=' . $inboundTrack;
                        } elseif ($order->fedex_track_num) {
                            $inboundTrack = $order->fedex_track_num;
                            $inboundCarrier = 'FedEx';
                            $inboundUrl = 'https://www.fedex.com/fedextrack/?trknbr=' . $inboundTrack;
                        } elseif ($order->dhl_track_num) {
                            $inboundTrack = $order->dhl_track_num;
                            $inboundCarrier = 'DHL';
                            $inboundUrl = 'https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id=' . $inboundTrack;
                        }
                    @endphp
                    <tr>
                        <td>
                            <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}" class="fw-semibold">{{ $order->orders_id }}</a>
                            @if($order->problem_reason)
                                <span class="badge bg-danger small">{{ $order->problem_reason }}</span>
                            @endif
                        </td>
                        <td>
                            @if($order->customer)
                                <a href="/{{ $prefix }}/customers/view/{{ $order->customer->customers_id }}">{{ $order->customer->billing_id }}</a>
                            @endif
                        </td>
                        <td class="text-nowrap small">
                            @if($dims->isNotEmpty())
                                {{ $dims->implode(' x ') }} in.
                            @endif
                        </td>
                        <td class="text-nowrap small">{{ $lbs }} lb, {{ $oz }} oz</td>
                        <td>{{ $order->mail_class }}</td>
                        <td class="small text-nowrap">
                            @if($inboundTrack)
                                <a href="#" class="text-decoration-none track-link"
                                   data-bs-toggle="modal" data-bs-target="#trackingModal"
                                   data-tracking="{{ $inboundTrack }}"
                                   data-carrier="{{ $inboundCarrier }}"
                                   data-carrier-url="{{ $inboundUrl }}">
                                    <span class="badge bg-light text-dark border me-1">{{ $inboundCarrier }}</span>...{{ substr($inboundTrack, -7) }}
                                </a>
                            @endif
                        </td>
                        <td>
                            @if(!empty($order->comments))
                                <button type="button" class="btn btn-link text-warning p-0 comment-pop"
                                   tabindex="0"
                                   data-bs-toggle="popover"
                                   data-bs-trigger="click"
                                   data-bs-placement="left"
                                   data-bs-content="{{ e($order->comments) }}">
                                    <i data-lucide="message-square" class="icon--sm"></i>
                                </button>
                            @endif
                        </td>
                        <td class="small text-nowrap">
                            @if($order->last_modified?->isToday()) Today
                            @elseif($order->last_modified) {{ $order->last_modified->format('n/j/y') }}
                            @endif
                        </td>
                        <td class="small text-nowrap">{{ $order->date_purchased?->format('n/j/y') }}</td>
                        <td class="text-end fw-semibold">${{ number_format($order->total?->value ?? 0, 2) }}</td>
                        <td class="text-nowrap">
                            <a href="mailto:{{ $order->customers_email_address }}" title="Email customer" class="text-muted me-1"><i data-lucide="mail" class="icon--sm"></i></a>
                            <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/charge" title="Charge" class="text-muted"><i data-lucide="credit-card" class="icon--sm"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($section['orders']->hasPages())
            <div class="d-flex justify-content-center mt-2">
                {{ $section['orders']->appends(request()->except($section['orders']->getPageName()))->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
    @endif
@endforeach

@if($paid->isEmpty() && $inWarehouse->isEmpty() && $problem->isEmpty())
    <div class="text-center py-5 text-muted">
        <i data-lucide="inbox" style="width:48px;height:48px" class="mb-3 opacity-50"></i>
        <p>No active orders</p>
    </div>
@endif

{{-- Tracking Modal --}}
<div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title d-flex align-items-center gap-2">
                    <i data-lucide="package" style="width:18px;height:18px"></i>
                    <span id="trackingModalCarrier"></span> Tracking
                </h6>
                <div class="d-flex align-items-center gap-2 ms-auto me-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="trackingCopyBtn" title="Copy tracking number">
                        <i data-lucide="copy" style="width:14px;height:14px;vertical-align:-2px"></i>
                    </button>
                    <a id="trackingCarrierLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary" title="Open on carrier site">
                        <i data-lucide="external-link" style="width:14px;height:14px;vertical-align:-2px"></i>
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                {{-- Loading state --}}
                <div id="trackingLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0 small">Fetching tracking info&hellip;</p>
                </div>
                {{-- Error state --}}
                <div id="trackingError" class="text-center py-5" style="display:none;">
                    <i data-lucide="alert-circle" style="width:36px;height:36px" class="text-danger mb-2"></i>
                    <p id="trackingErrorMsg" class="text-muted mb-0"></p>
                </div>
                {{-- Tracking data --}}
                <div id="trackingContent" style="display:none;">
                    {{-- Status bar --}}
                    <div class="px-3 py-3 border-bottom bg-light">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <span id="trackingCarrierBadge" class="badge me-2"></span>
                                <code id="trackingModalNumber" class="user-select-all small"></code>
                            </div>
                            <div>
                                <span id="trackingStatusBadge" class="badge bg-success fs-6"></span>
                            </div>
                        </div>
                        <div id="trackingEstDelivery" class="text-muted small mt-1" style="display:none;">
                            <i data-lucide="calendar" style="width:13px;height:13px;vertical-align:-2px" class="me-1"></i>
                            Est. delivery: <strong id="trackingEstDate"></strong>
                        </div>
                    </div>
                    {{-- Events timeline --}}
                    <div class="px-3 py-3" style="max-height:400px;overflow-y:auto;">
                        <div id="trackingEvents"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize comment popovers — dismiss when clicking outside
    var popovers = [];
    document.querySelectorAll('.comment-pop').forEach(function(el) {
        popovers.push(new bootstrap.Popover(el, { html: false, sanitize: true }));
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.comment-pop') && !e.target.closest('.popover')) {
            popovers.forEach(function(p) { p.hide(); });
        }
    });

    // Tracking modal — fetch real tracking data from backend
    var trackModal = document.getElementById('trackingModal');
    if (trackModal) {
        var carrierColors = { USPS: 'bg-primary', UPS: 'bg-warning text-dark', FedEx: 'bg-info text-dark', DHL: 'bg-danger' };
        var prefix = '{{ $prefix }}';
        var currentTrackNum = '';

        trackModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (!btn) return;
            var trackNum = btn.dataset.tracking;
            var carrier = btn.dataset.carrier;
            var carrierUrl = btn.dataset.carrierUrl;
            currentTrackNum = trackNum;

            // Set header info
            document.getElementById('trackingModalCarrier').textContent = carrier;
            document.getElementById('trackingCarrierLink').href = carrierUrl;

            // Reset states
            document.getElementById('trackingLoading').style.display = '';
            document.getElementById('trackingError').style.display = 'none';
            document.getElementById('trackingContent').style.display = 'none';

            // Fetch tracking data
            fetch('/' + prefix + '/tracking/' + encodeURIComponent(carrier) + '/' + encodeURIComponent(trackNum))
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    document.getElementById('trackingLoading').style.display = 'none';

                    if (data.error) {
                        document.getElementById('trackingErrorMsg').textContent = data.error;
                        document.getElementById('trackingError').style.display = '';
                        if (window.lucide) lucide.createIcons();
                        return;
                    }

                    // Populate status bar
                    var badge = document.getElementById('trackingCarrierBadge');
                    badge.className = 'badge ' + (carrierColors[data.carrier] || 'bg-secondary');
                    badge.textContent = data.carrier;

                    document.getElementById('trackingModalNumber').textContent = data.tracking_number;

                    var statusBadge = document.getElementById('trackingStatusBadge');
                    var statusLower = (data.status || '').toLowerCase();
                    var statusColor = 'bg-secondary';
                    if (statusLower.indexOf('delivered') !== -1) statusColor = 'bg-success';
                    else if (statusLower.indexOf('transit') !== -1 || statusLower.indexOf('accepted') !== -1) statusColor = 'bg-primary';
                    else if (statusLower.indexOf('exception') !== -1 || statusLower.indexOf('alert') !== -1) statusColor = 'bg-danger';
                    else if (statusLower.indexOf('out for delivery') !== -1) statusColor = 'bg-info';
                    statusBadge.className = 'badge ' + statusColor + ' fs-6';
                    statusBadge.textContent = data.status;

                    // Estimated delivery
                    var estWrap = document.getElementById('trackingEstDelivery');
                    if (data.estimated_delivery) {
                        document.getElementById('trackingEstDate').textContent = data.estimated_delivery;
                        estWrap.style.display = '';
                    } else {
                        estWrap.style.display = 'none';
                    }

                    // Build events timeline
                    var eventsContainer = document.getElementById('trackingEvents');
                    eventsContainer.innerHTML = '';

                    if (data.events && data.events.length > 0) {
                        data.events.forEach(function(evt, i) {
                            var isFirst = i === 0;
                            var dot = document.createElement('div');
                            dot.className = 'd-flex gap-3 mb-0';
                            dot.innerHTML =
                                '<div class="d-flex flex-column align-items-center" style="min-width:12px">' +
                                    '<div style="width:10px;height:10px;border-radius:50%;margin-top:5px" class="' + (isFirst ? 'bg-success' : 'bg-secondary opacity-50') + '"></div>' +
                                    (i < data.events.length - 1 ? '<div style="width:2px;flex:1;min-height:20px" class="bg-secondary opacity-25"></div>' : '') +
                                '</div>' +
                                '<div class="pb-3 flex-grow-1">' +
                                    '<div class="' + (isFirst ? 'fw-semibold' : 'small') + '">' + escapeHtml(evt.description) + '</div>' +
                                    '<div class="text-muted" style="font-size:0.78rem">' +
                                        (evt.location ? '<span class="me-2">' + escapeHtml(evt.location) + '</span>' : '') +
                                        (evt.date ? '<span>' + escapeHtml(evt.date) + '</span>' : '') +
                                    '</div>' +
                                '</div>';
                            eventsContainer.appendChild(dot);
                        });
                    } else {
                        eventsContainer.innerHTML = '<p class="text-muted text-center mb-0">No tracking events available yet.</p>';
                    }

                    document.getElementById('trackingContent').style.display = '';
                    if (window.lucide) lucide.createIcons();
                })
                .catch(function(err) {
                    document.getElementById('trackingLoading').style.display = 'none';
                    document.getElementById('trackingErrorMsg').textContent = 'Could not load tracking data.';
                    document.getElementById('trackingError').style.display = '';
                    if (window.lucide) lucide.createIcons();
                });
        });

        // Copy tracking number
        document.getElementById('trackingCopyBtn').addEventListener('click', function() {
            if (!currentTrackNum) return;
            navigator.clipboard.writeText(currentTrackNum).then(function() {
                var btn = document.getElementById('trackingCopyBtn');
                btn.innerHTML = '<i data-lucide="check" style="width:14px;height:14px;vertical-align:-2px"></i>';
                if (window.lucide) lucide.createIcons();
                setTimeout(function() {
                    btn.innerHTML = '<i data-lucide="copy" style="width:14px;height:14px;vertical-align:-2px"></i>';
                    if (window.lucide) lucide.createIcons();
                }, 2000);
            });
        });
    }

    // HTML escaping helper
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }
});
</script>
@endpush
