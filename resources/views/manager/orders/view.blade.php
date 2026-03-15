@extends('layouts.manager')
@section('title', 'Order #' . $order->orders_id . ' - APO Box Admin')
@section('content')
@php
    $prefix = auth('admin')->user()->routePrefix();
    $lbs = intdiv((int)($order->weight_oz ?? 0), 16);
    $oz = (int)($order->weight_oz ?? 0) % 16;
    $dims = collect([$order->length, $order->width, $order->depth])
        ->filter()
        ->map(fn($v) => rtrim(rtrim(number_format((float)$v, 2), '0'), '.'));
    $inbound = $order->usps_track_num_in ?: $order->ups_track_num ?: $order->fedex_track_num ?: $order->dhl_track_num ?: '';
    $inboundCarrier = match(true) {
        !empty($order->usps_track_num_in) => 'USPS',
        !empty($order->ups_track_num) => 'UPS',
        !empty($order->fedex_track_num) => 'FedEx',
        !empty($order->dhl_track_num) => 'DHL',
        default => '',
    };
    $inboundUrl = match($inboundCarrier) {
        'USPS' => 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . $inbound,
        'UPS' => 'https://www.ups.com/track?tracknum=' . $inbound,
        'FedEx' => 'https://www.fedex.com/fedextrack/?trknbr=' . $inbound,
        'DHL' => 'https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id=' . $inbound,
        default => '',
    };
    $outboundUrl = $order->usps_track_num
        ? 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . $order->usps_track_num
        : '';

    // Build clean charge lines from individual relations
    $chargeLines = collect([
        ['label' => 'Shipping', 'value' => $order->shipping?->value],
        ['label' => 'Handling Fee', 'value' => $order->fee?->value],
        ['label' => 'Insurance', 'value' => $order->insurance?->value],
        ['label' => 'Storage', 'value' => $order->storage?->value],
        ['label' => 'Repack', 'value' => $order->repack?->value],
        ['label' => 'Inspection', 'value' => $order->inspection?->value],
        ['label' => 'Return', 'value' => $order->returnItem?->value],
        ['label' => 'Misaddressed', 'value' => $order->misaddressed?->value],
        ['label' => 'Ship to US', 'value' => $order->shipToUS?->value],
    ])->filter(fn($c) => (float)($c['value'] ?? 0) != 0);
@endphp

{{-- Page Header --}}
<x-page-header title="Order #{{ $order->orders_id }}">
    <x-slot:actions>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <x-status-badge :status="$order->status?->orders_status_name" class="fs-6 px-3 py-2" />
            @if($order->problem_reason)
                <span class="badge bg-danger fs-6 px-3 py-2">{{ $order->problem_reason }}</span>
            @endif
            @if($order->zendesk_ticket_id)
                <button type="button" class="badge bg-warning text-dark fs-6 px-3 py-2 border-0"
                    data-bs-toggle="modal" data-bs-target="#zendeskModal" style="cursor:pointer">
                    <i data-lucide="message-circle" class="icon"></i> #{{ $order->zendesk_ticket_id }}
                </button>
            @endif
            @if($order->customer?->billing_id)
                <a href="/{{ $prefix }}/customers/view/{{ $order->customer->customers_id }}" class="badge bg-primary fs-6 px-3 py-2 text-decoration-none">{{ $order->customer->billing_id }}</a>
            @endif
            <span class="text-muted small ms-1">
                {{ $order->date_purchased?->format('M jS, Y') ?? '' }}
                @if($creator) &middot; {{ $creator }} @endif
            </span>
        </div>
    </x-slot:actions>
</x-page-header>

{{-- Customer, Package & Charges overview --}}
<div class="row mb-4">
    <div class="col-lg-4 d-flex">
        <x-detail-card title="Customer" class="flex-fill detail-card--compact">
            <x-detail-row label="Name">
                <a href="/{{ $prefix }}/customers/view/{{ $order->customer?->customers_id }}" class="fw-semibold">{{ $order->customers_name }}</a>
                @if($order->customer?->billing_id)
                    <span class="badge bg-primary ms-1">{{ $order->customer->billing_id }}</span>
                @endif
            </x-detail-row>
            <x-detail-row label="Email"><a href="mailto:{{ $order->customers_email_address }}" class="text-break">{{ $order->customers_email_address }}</a></x-detail-row>
            <x-detail-row label="Phone">{{ $order->customers_telephone ?: 'N/A' }}</x-detail-row>
            @if($order->customer)
                <x-detail-row label="Card">
                    {{ $order->customer->masked_cc_number ?: 'None' }}
                    @if($order->customer->cc_expires)
                        <span class="text-muted small ms-1">(exp {{ $order->customer->cc_expires }})</span>
                    @endif
                </x-detail-row>
            @endif
        </x-detail-card>
    </div>
    <div class="col-lg-4 d-flex">
        <x-detail-card title="Package" class="flex-fill detail-card--compact">
            <x-detail-row label="Dimensions">{{ $dims->isNotEmpty() ? $dims->implode(' × ') . ' in.' : 'N/A' }}</x-detail-row>
            <x-detail-row label="Weight">{{ $lbs }} lb, {{ $oz }} oz</x-detail-row>
            <x-detail-row label="Mail Class">{{ $order->mail_class ?: 'N/A' }}</x-detail-row>
            <x-detail-row label="Pkg Type">{{ $order->package_type ?: 'N/A' }}</x-detail-row>
            @if($order->customs_description)
                <x-detail-row label="Customs">{{ $order->customs_description }}</x-detail-row>
            @endif
        </x-detail-card>
    </div>
    <div class="col-lg-4 d-flex">
        <x-detail-card title="Charges" class="flex-fill detail-card--compact">
            <table class="table table-sm table-borderless mb-0">
                <tbody>
                    @forelse($chargeLines as $line)
                        <tr>
                            <td class="text-muted ps-0">{{ $line['label'] }}</td>
                            <td class="text-end pe-0">${{ number_format((float)$line['value'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td class="text-muted ps-0" colspan="2">No charges yet</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold border-top">
                        <td class="ps-0">Total</td>
                        <td class="text-end pe-0">${{ number_format((float)($order->total?->value ?? 0), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-2">
                <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/charge" class="btn btn-sm btn-outline-success w-100">
                    <i data-lucide="credit-card" class="icon--sm me-1"></i>Charge / Edit Totals
                </a>
            </div>
        </x-detail-card>
    </div>
</div>

{{-- Main two-column layout --}}
<div class="row">
    {{-- Left column --}}
    <div class="col-lg-7">
        {{-- Tracking --}}
        <x-detail-card title="Tracking">
            <x-detail-row label="Inbound">
                @if($inbound)
                    <span class="badge bg-light text-dark border me-1">{{ $inboundCarrier }}</span>
                    <button type="button" class="btn btn-link p-0 text-decoration-none fw-semibold tracking-link"
                        data-bs-toggle="modal" data-bs-target="#trackingModal"
                        data-tracking="{{ $inbound }}" data-carrier="{{ $inboundCarrier }}" data-carrier-url="{{ $inboundUrl }}">
                        {{ $inbound }}
                    </button>
                    <a href="{{ $inboundUrl }}" target="_blank" class="ms-1 text-muted" title="Open on {{ $inboundCarrier }}">
                        <i data-lucide="external-link" class="icon--xs"></i>
                    </a>
                @else
                    <span class="text-muted">None</span>
                @endif
            </x-detail-row>
            <x-detail-row label="Outbound">
                @if($order->usps_track_num)
                    <span class="badge bg-light text-dark border me-1">USPS</span>
                    <button type="button" class="btn btn-link p-0 text-decoration-none fw-semibold tracking-link"
                        data-bs-toggle="modal" data-bs-target="#trackingModal"
                        data-tracking="{{ $order->usps_track_num }}" data-carrier="USPS" data-carrier-url="{{ $outboundUrl }}">
                        {{ $order->usps_track_num }}
                    </button>
                    <a href="{{ $outboundUrl }}" target="_blank" class="ms-1 text-muted" title="Open on USPS">
                        <i data-lucide="external-link" class="icon--xs"></i>
                    </a>
                @else
                    <span class="text-muted">None</span>
                @endif
            </x-detail-row>
        </x-detail-card>

        {{-- Addresses --}}
        <div class="row">
            <div class="col-md-4">
                <x-address-card label="Billing">
                    {{ $order->billing_name ?: $order->customers_name }}
                    @if($order->billing_company)<br>{{ $order->billing_company }}@endif
                    <br>{{ $order->billing_street_address }}
                    @if($order->billing_suburb)<br>{{ $order->billing_suburb }}@endif
                    <br>{{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postcode }}
                </x-address-card>
            </div>
            <div class="col-md-4">
                <x-address-card label="From (Warehouse)">
                    {{ $order->customers_name }}
                    @if($order->customer?->billing_id)<br>{{ $order->customer->billing_id }}@endif
                    <br>{{ $order->customers_street_address }}
                    @if($order->customers_suburb)<br>{{ $order->customers_suburb }}@endif
                    <br>{{ $order->customers_city }}, {{ $order->customers_state }} {{ $order->customers_postcode }}
                </x-address-card>
            </div>
            <div class="col-md-4">
                <x-address-card label="To (Delivery)">
                    {{ $order->delivery_name }}
                    @if($order->delivery_company)<br>{{ $order->delivery_company }}@endif
                    <br>{{ $order->delivery_street_address }}
                    @if($order->delivery_suburb)<br>{{ $order->delivery_suburb }}@endif
                    <br>{{ $order->delivery_city }}, {{ $order->delivery_state }} {{ $order->delivery_postcode }}
                    <br>{{ $order->delivery_country }}
                </x-address-card>
            </div>
        </div>

        {{-- Comments --}}
        @if($order->comments)
            <x-detail-card title="Comments">
                <div class="small">{{ $order->comments }}</div>
            </x-detail-card>
        @endif
    </div>

    {{-- Right column --}}
    <div class="col-lg-5">
        {{-- Update Status --}}
        <x-detail-card title="Update Status">
            <form method="POST" action="/{{ $prefix }}/orders/{{ $order->orders_id }}/update-status">
                @csrf
                <div class="mb-2">
                    <select name="orders_status" id="ordersStatus" class="form-select form-select-sm">
                        @foreach($ordersStatuses as $sid => $sname)
                            <option value="{{ $sid }}" @selected($order->orders_status == $sid)>{{ $sname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2" id="problemReasonWrap" style="display: none;">
                    <select name="problem_reason" id="problemReason" class="form-select form-select-sm">
                        <option value="">Select reason...</option>
                        @foreach(['Prohibited Item', 'Unidentified', 'Returned', 'Error Code', 'Oversized/Overweight', 'Damaged', 'Account Closed', 'Invalid Zip Code', 'Hold For Quote'] as $reason)
                            <option value="{{ $reason }}" @selected($order->problem_reason === $reason)>{{ $reason }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <textarea name="status_history_comments" class="form-control form-control-sm" rows="2" placeholder="Comments"></textarea>
                </div>
                <div class="mb-2">
                    <input type="text" name="usps_track_num" class="form-control form-control-sm" placeholder="Outbound tracking number" value="{{ $order->usps_track_num }}">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="notify_customer" id="notifyCustomer" value="1">
                    <label class="form-check-label" for="notifyCustomer">Notify Customer</label>
                </div>
                <button type="submit" class="btn btn-sm btn-warning fw-semibold w-100">
                    <i data-lucide="refresh-cw" class="icon--sm me-1"></i>Update Status
                </button>
            </form>
        </x-detail-card>

        {{-- Support Ticket --}}
        <x-detail-card title="Support Ticket">
            @if($order->zendesk_ticket_id)
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="fw-semibold">Ticket #{{ $order->zendesk_ticket_id }}</span>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#zendeskModal">
                            <i data-lucide="messages-square" class="icon--sm me-1"></i>View
                        </button>
                        <a href="https://apobox.zendesk.com/agent/tickets/{{ $order->zendesk_ticket_id }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Open in Zendesk">
                            <i data-lucide="external-link" class="icon--sm"></i>
                        </a>
                    </div>
                </div>
            @elseif($order->orders_status == 6)
                <p class="text-muted small mb-2">No ticket linked to this order.</p>
                <form method="POST" action="{{ route($prefix . '.orders.zendesk-ticket', $order->orders_id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning w-100">
                        <i data-lucide="plus" class="icon--sm me-1"></i>Create Zendesk Ticket
                    </button>
                </form>
            @else
                <p class="text-muted small mb-0">No ticket for this order.</p>
            @endif
        </x-detail-card>

        {{-- Actions --}}
        <x-detail-card title="Actions">
            <div class="d-grid gap-2">
                <a href="/{{ $prefix }}/customers/view/{{ $order->customer?->customers_id }}" class="btn btn-sm btn-outline-secondary">
                    <i data-lucide="user" class="icon--sm me-1"></i>Go to Customer
                </a>
                @if($mailClass === 'usps')
                    <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/print_label" class="btn btn-sm btn-outline-secondary">
                        <i data-lucide="printer" class="icon--sm me-1"></i>Print USPS Postage
                    </a>
                @elseif($mailClass === 'fedex')
                    <a href="{{ $labelUrl }}" class="btn btn-sm btn-outline-secondary">
                        <i data-lucide="printer" class="icon--sm me-1"></i>{{ $labelAction }} FedEx Label
                    </a>
                @elseif($mailClass === 'ups')
                    <a href="{{ $labelUrl }}" class="btn btn-sm btn-outline-secondary">
                        <i data-lucide="printer" class="icon--sm me-1"></i>{{ $labelAction }} UPS Label
                    </a>
                @endif
                @if($mailClass !== 'usps' && $reprint)
                    <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/delete_label" class="btn btn-sm btn-outline-warning" onclick="return confirm('Delete label?')">
                        <i data-lucide="x" class="icon--sm me-1"></i>Delete Label
                    </a>
                @endif
                <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/print_label" class="btn btn-sm btn-outline-secondary">
                    <i data-lucide="tag" class="icon--sm me-1"></i>Print Zebra Label
                </a>
            </div>
        </x-detail-card>
    </div>
</div>

{{-- Status History --}}
<x-table-card title="Status History">
    <div class="table-responsive">
        <table class="table table-modern table-sm">
            <thead><tr><th>Time</th><th>Status</th><th>Comments</th><th>Notified</th></tr></thead>
            <tbody>
                @foreach($statusHistories as $history)
                    <tr>
                        <td class="small text-nowrap">{{ $history->date_added?->format('g:ia M jS, Y') }}</td>
                        <td><x-status-badge :status="$history->status?->orders_status_name" /></td>
                        <td class="small">{{ $history->comments }}</td>
                        <td>
                            @if($history->customer_notified)
                                <span class="badge bg-success"><i data-lucide="check" class="icon--xs"></i></span>
                            @else
                                <span class="badge bg-danger"><i data-lucide="x" class="icon--xs"></i></span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-table-card>

{{-- Delete order --}}
<div class="mt-1 mb-4">
    <a href="/{{ $prefix }}/orders/delete/{{ $order->orders_id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this order? This cannot be undone.')">
        <i data-lucide="trash-2" class="icon--sm me-1"></i>Delete Order
    </a>
</div>

{{-- ======== TRACKING MODAL ======== --}}
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
                <div id="trackingLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0 small">Fetching tracking info&hellip;</p>
                </div>
                <div id="trackingError" class="text-center py-5" style="display:none;">
                    <i data-lucide="alert-circle" style="width:36px;height:36px" class="text-danger mb-2"></i>
                    <p id="trackingErrorMsg" class="text-muted mb-0"></p>
                </div>
                <div id="trackingContent" style="display:none;">
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
                    <div class="px-3 py-3" style="max-height:400px;overflow-y:auto;">
                        <div id="trackingEvents"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ======== ZENDESK MODAL ======== --}}
@if($order->zendesk_ticket_id)
<div class="modal fade" id="zendeskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title d-flex align-items-center gap-2">
                    <i data-lucide="message-circle" style="width:18px;height:18px"></i>
                    <span>Ticket #{{ $order->zendesk_ticket_id }}</span>
                    <span id="zendeskStatusBadge" class="badge bg-secondary ms-1" style="display:none;"></span>
                </h6>
                <div class="d-flex align-items-center gap-2 ms-auto me-2">
                    <a href="https://apobox.zendesk.com/agent/tickets/{{ $order->zendesk_ticket_id }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Open in Zendesk">
                        <i data-lucide="external-link" style="width:14px;height:14px;vertical-align:-2px"></i>
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="zendeskLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0 small">Loading conversation&hellip;</p>
                </div>
                <div id="zendeskError" class="text-center py-5" style="display:none;">
                    <i data-lucide="alert-circle" style="width:36px;height:36px" class="text-danger mb-2"></i>
                    <p id="zendeskErrorMsg" class="text-muted mb-0"></p>
                </div>
                <div id="zendeskContent" style="display:none;">
                    <div id="zendeskSubject" class="px-3 py-2 border-bottom bg-light fw-semibold small"></div>
                    <div id="zendeskComments" class="px-3 py-3" style="max-height:400px;overflow-y:auto;"></div>
                    <div class="border-top px-3 py-3">
                        <form id="zendeskReplyForm">
                            <div class="mb-2">
                                <textarea id="zendeskReplyBody" class="form-control form-control-sm" rows="3" placeholder="Type a reply..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Sends as a public reply on Zendesk</small>
                                <button type="submit" class="btn btn-sm btn-primary" id="zendeskReplyBtn">
                                    <i data-lucide="send" class="icon--sm me-1"></i>Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var prefix = '{{ $prefix }}';
    var orderId = '{{ $order->orders_id }}';

    // ——— Problem reason toggle ———
    var statusSelect = document.getElementById('ordersStatus');
    var reasonWrap = document.getElementById('problemReasonWrap');
    var reasonSelect = document.getElementById('problemReason');

    function toggleProblemReason() {
        var isProblem = statusSelect.value === '6';
        reasonWrap.style.display = isProblem ? '' : 'none';
        reasonSelect.required = isProblem;
        if (!isProblem) reasonSelect.value = '';
    }

    statusSelect.addEventListener('change', toggleProblemReason);
    toggleProblemReason();

    // ——— HTML escaping helper ———
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    // ——— TRACKING MODAL ———
    var trackModal = document.getElementById('trackingModal');
    if (trackModal) {
        var carrierColors = { USPS: 'bg-primary', UPS: 'bg-warning text-dark', FedEx: 'bg-info text-dark', DHL: 'bg-danger' };
        var currentTrackNum = '';

        trackModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (!btn) return;
            var trackNum = btn.dataset.tracking;
            var carrier = btn.dataset.carrier;
            var carrierUrl = btn.dataset.carrierUrl;
            currentTrackNum = trackNum;

            document.getElementById('trackingModalCarrier').textContent = carrier;
            document.getElementById('trackingCarrierLink').href = carrierUrl;

            document.getElementById('trackingLoading').style.display = '';
            document.getElementById('trackingError').style.display = 'none';
            document.getElementById('trackingContent').style.display = 'none';

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

                    // Status bar
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

                    // Events timeline
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
                .catch(function() {
                    document.getElementById('trackingLoading').style.display = 'none';
                    document.getElementById('trackingErrorMsg').textContent = 'Could not load tracking data.';
                    document.getElementById('trackingError').style.display = '';
                    if (window.lucide) lucide.createIcons();
                });
        });

        document.getElementById('trackingCopyBtn').addEventListener('click', function() {
            if (!currentTrackNum) return;
            var btn = this;
            navigator.clipboard.writeText(currentTrackNum).then(function() {
                btn.innerHTML = '<i data-lucide="check" style="width:14px;height:14px;vertical-align:-2px"></i>';
                if (window.lucide) lucide.createIcons();
                setTimeout(function() {
                    btn.innerHTML = '<i data-lucide="copy" style="width:14px;height:14px;vertical-align:-2px"></i>';
                    if (window.lucide) lucide.createIcons();
                }, 2000);
            });
        });
    }

    // ——— ZENDESK MODAL ———
    var zdModal = document.getElementById('zendeskModal');
    if (zdModal) {
        var zdLoaded = false;

        zdModal.addEventListener('show.bs.modal', function() {
            if (zdLoaded) return; // only fetch once per page load

            document.getElementById('zendeskLoading').style.display = '';
            document.getElementById('zendeskError').style.display = 'none';
            document.getElementById('zendeskContent').style.display = 'none';

            fetch('/' + prefix + '/orders/' + orderId + '/zendesk-comments')
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    document.getElementById('zendeskLoading').style.display = 'none';

                    if (data.error) {
                        document.getElementById('zendeskErrorMsg').textContent = data.error;
                        document.getElementById('zendeskError').style.display = '';
                        if (window.lucide) lucide.createIcons();
                        return;
                    }

                    // Ticket subject + status
                    if (data.ticket) {
                        document.getElementById('zendeskSubject').textContent = data.ticket.subject || '';
                        var sBadge = document.getElementById('zendeskStatusBadge');
                        var st = (data.ticket.status || '').toLowerCase();
                        var sColor = { new: 'bg-info', open: 'bg-warning text-dark', pending: 'bg-primary', solved: 'bg-success', closed: 'bg-secondary' };
                        sBadge.className = 'badge ms-1 ' + (sColor[st] || 'bg-secondary');
                        sBadge.textContent = st.charAt(0).toUpperCase() + st.slice(1);
                        sBadge.style.display = '';
                    }

                    // Render comments
                    renderZendeskComments(data.comments || []);
                    document.getElementById('zendeskContent').style.display = '';
                    zdLoaded = true;
                    if (window.lucide) lucide.createIcons();
                })
                .catch(function() {
                    document.getElementById('zendeskLoading').style.display = 'none';
                    document.getElementById('zendeskErrorMsg').textContent = 'Could not load ticket conversation.';
                    document.getElementById('zendeskError').style.display = '';
                    if (window.lucide) lucide.createIcons();
                });
        });

        function renderZendeskComments(comments) {
            var container = document.getElementById('zendeskComments');
            container.innerHTML = '';

            if (comments.length === 0) {
                container.innerHTML = '<p class="text-muted text-center mb-0">No comments yet.</p>';
                return;
            }

            comments.forEach(function(c, i) {
                var isFirst = i === 0;
                var date = c.created_at ? new Date(c.created_at).toLocaleString() : '';
                var el = document.createElement('div');
                el.className = 'mb-3 p-3 rounded-3 ' + (isFirst ? 'bg-light border' : 'bg-white border');
                el.innerHTML =
                    '<div class="d-flex justify-content-between align-items-center mb-1">' +
                        '<span class="fw-semibold small">' + (isFirst ? 'Initial Message' : 'Reply') + '</span>' +
                        '<span class="text-muted" style="font-size:0.75rem">' + escapeHtml(date) + '</span>' +
                    '</div>' +
                    '<div class="small" style="white-space:pre-wrap;">' + escapeHtml(c.body) + '</div>';
                container.appendChild(el);
            });

            // Scroll to bottom
            container.scrollTop = container.scrollHeight;
        }

        // Reply form
        var replyForm = document.getElementById('zendeskReplyForm');
        if (replyForm) {
            replyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var body = document.getElementById('zendeskReplyBody').value.trim();
                if (!body) return;

                var btn = document.getElementById('zendeskReplyBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';

                fetch('/' + prefix + '/orders/' + orderId + '/zendesk-reply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ body: body }),
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="send" class="icon--sm me-1"></i>Send Reply';
                    if (window.lucide) lucide.createIcons();

                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    // Append the reply to the thread
                    var container = document.getElementById('zendeskComments');
                    var el = document.createElement('div');
                    el.className = 'mb-3 p-3 rounded-3 bg-white border';
                    el.innerHTML =
                        '<div class="d-flex justify-content-between align-items-center mb-1">' +
                            '<span class="fw-semibold small">Reply</span>' +
                            '<span class="text-muted" style="font-size:0.75rem">' + new Date().toLocaleString() + '</span>' +
                        '</div>' +
                        '<div class="small" style="white-space:pre-wrap;">' + escapeHtml(body) + '</div>';
                    container.appendChild(el);
                    container.scrollTop = container.scrollHeight;

                    document.getElementById('zendeskReplyBody').value = '';
                })
                .catch(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="send" class="icon--sm me-1"></i>Send Reply';
                    if (window.lucide) lucide.createIcons();
                    alert('Failed to send reply. Please try again.');
                });
            });
        }
    }
});
</script>
@endpush
