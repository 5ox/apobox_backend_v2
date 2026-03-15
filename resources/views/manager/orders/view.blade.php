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
@endphp

{{-- Page Header --}}
<x-page-header title="Order #{{ $order->orders_id }}">
    <x-slot:actions>
        <div class="d-flex align-items-center gap-2">
            <x-status-badge :status="$order->status?->orders_status_name" class="fs-6 px-3 py-2" />
            @if($order->problem_reason)
                <span class="badge bg-danger fs-6 px-3 py-2">{{ $order->problem_reason }}</span>
            @endif
            @if($order->zendesk_ticket_id)
                <a href="https://apobox.zendesk.com/agent/tickets/{{ $order->zendesk_ticket_id }}" target="_blank" class="badge bg-warning text-dark fs-6 px-3 py-2 text-decoration-none">
                    <i data-lucide="message-circle" class="icon"></i> Ticket #{{ $order->zendesk_ticket_id }}
                </a>
            @endif
            @if($order->customer?->billing_id)
                <a href="/{{ $prefix }}/customers/view/{{ $order->customer->customers_id }}" class="badge bg-primary fs-6 px-3 py-2 text-decoration-none">{{ $order->customer->billing_id }}</a>
            @endif
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
            @if($order->customer?->insurance_fee)
                <x-detail-row label="Insurance">${{ number_format($order->customer->insurance_fee, 2) }}</x-detail-row>
            @endif
            @if($order->customs_description)
                <x-detail-row label="Customs">{{ $order->customs_description }}</x-detail-row>
            @endif
        </x-detail-card>
    </div>
    <div class="col-lg-4 d-flex">
        <x-detail-card title="Charges" class="flex-fill">
            <table class="table table-sm table-borderless mb-0 small">
                <tbody>
                    @foreach($orderCharges as $charge)
                        <tr @if(in_array($charge->class, ['ot_subtotal', 'ot_total'])) class="fw-bold" @endif>
                            <td>{{ $charge->title }}</td>
                            <td class="text-end">${{ number_format($charge->value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
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
                    <a href="{{ $inboundUrl }}" target="_blank" class="text-decoration-none">{{ $inbound }}</a>
                @else
                    <span class="text-muted">None</span>
                @endif
            </x-detail-row>
            <x-detail-row label="Outbound">
                @if($order->usps_track_num)
                    <a href="https://tools.usps.com/go/TrackConfirmAction?tLabels={{ $order->usps_track_num }}" target="_blank" class="text-decoration-none">{{ $order->usps_track_num }}</a>
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

        {{-- Order Meta --}}
        <x-detail-card title="Order Info">
            <x-detail-row label="Processed">{{ $order->date_purchased?->format('g:ia M jS, Y') ?? 'N/A' }}</x-detail-row>
            <x-detail-row label="Last Modified">{{ $order->last_modified?->isToday() ? 'Today' : $order->last_modified?->format('M jS, Y') }}</x-detail-row>
            @if($creator)
                <x-detail-row label="Created By">{{ $creator }}</x-detail-row>
            @endif
        </x-detail-card>
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
                    <span class="fw-semibold">Ticket #{{ $order->zendesk_ticket_id }}</span>
                    <a href="https://apobox.zendesk.com/agent/tickets/{{ $order->zendesk_ticket_id }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i data-lucide="external-link" class="icon--sm me-1"></i>Open in Zendesk
                    </a>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusSelect = document.getElementById('ordersStatus');
        const reasonWrap = document.getElementById('problemReasonWrap');
        const reasonSelect = document.getElementById('problemReason');

        function toggleProblemReason() {
            const isProblem = statusSelect.value === '6';
            reasonWrap.style.display = isProblem ? '' : 'none';
            reasonSelect.required = isProblem;
            if (!isProblem) reasonSelect.value = '';
        }

        statusSelect.addEventListener('change', toggleProblemReason);
        toggleProblemReason();
    });
</script>
@endpush
