@extends('layouts.manager')
@section('title', 'Order #' . $order->orders_id . ' - APO Box Admin')
@section('content')
@php
    $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee';
    $lbs = intdiv((int)($order->weight_oz ?? 0), 16);
    $oz = (int)($order->weight_oz ?? 0) % 16;
    $dims = collect([$order->length, $order->width, $order->depth])
        ->filter()
        ->map(fn($v) => rtrim(rtrim(number_format((float)$v, 2), '0'), '.'));
    $inbound = $order->usps_track_num_in ?: $order->ups_track_num ?: $order->fedex_track_num ?: $order->dhl_track_num ?: '';
@endphp

{{-- Header: Order # (left) + Status (right) --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
    <h1 class="h3 fw-bold mb-0">Order #{{ $order->orders_id }}</h1>
    <div class="d-flex align-items-center gap-2">
        <span class="h5 mb-0 text-muted">Status:</span>
        <x-status-badge :status="$order->status?->orders_status_name" class="fs-6 px-3 py-2" />
    </div>
</div>
<hr class="mt-1 mb-3">

{{-- Main two-column layout --}}
<div class="row">
    {{-- Left column: Order info --}}
    <div class="col-lg-7">
        <p class="text-muted mb-2">
            Updated {{ $order->last_modified?->isToday() ? 'Today' : $order->last_modified?->format('M jS, Y') }}
        </p>

        <div class="mb-3">
            <strong>Inbound:</strong>
            @if($inbound)
                <a href="https://tools.usps.com/go/TrackConfirmAction?tLabels={{ $inbound }}" target="_blank" class="text-decoration-none">{{ $inbound }}</a>
            @else
                <span class="text-muted">None</span>
            @endif
        </div>
        <div class="mb-4">
            <strong>Outbound:</strong>
            @if($order->usps_track_num)
                <a href="https://tools.usps.com/go/TrackConfirmAction?tLabels={{ $order->usps_track_num }}" target="_blank" class="text-decoration-none">{{ $order->usps_track_num }}</a>
            @else
                <span class="text-muted">None</span>
            @endif
        </div>
    </div>

    {{-- Right column: Status update + actions --}}
    <div class="col-lg-5">
        <form method="POST" action="/{{ $prefix }}/orders/{{ $order->orders_id }}/update-status">
            @csrf
            <div class="mb-2 d-flex align-items-center gap-2">
                <label class="fw-semibold text-nowrap">Status:</label>
                <select name="orders_status" class="form-select form-select-sm">
                    @foreach($ordersStatuses as $sid => $sname)
                        <option value="{{ $sid }}" @selected($order->orders_status == $sid)>{{ $sname }}</option>
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
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-sm btn-outline-warning fw-semibold">UPDATE STATUS</button>
            </div>
        </form>
        <div class="d-grid gap-2 mt-2">
            <a href="mailto:{{ $order->customers_email_address }}" class="btn btn-sm btn-outline-secondary">EMAIL CUSTOMER</a>
            <a href="/{{ $prefix }}/customers/view/{{ $order->customer?->customers_id }}" class="btn btn-sm btn-outline-secondary">GO TO CUSTOMER</a>
            @if($mailClass === 'usps')
                <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/print_label" class="btn btn-sm btn-outline-secondary">PRINT USPS POSTAGE</a>
            @else
                <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary">{{ strtoupper($action) }} FEDEX LABEL</a>
            @endif
            <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/print_label" class="btn btn-sm btn-outline-secondary">PRINT LABEL</a>
        </div>
    </div>
</div>

{{-- Package details row --}}
<div class="row mt-3 mb-4">
    <div class="col-md-4">
        <div class="text-muted small">
            Dimensions: {{ $dims->isNotEmpty() ? $dims->implode(' x ') . ' in.' : 'N/A' }}<br>
            Weight: {{ $lbs }} lb, {{ $oz }} oz
        </div>
    </div>
    <div class="col-md-4">
        <div class="text-muted small">
            Class: {{ $order->mail_class ?: 'N/A' }}<br>
            Type: {{ $order->package_type ?: 'N/A' }}
        </div>
    </div>
    <div class="col-md-4">
        <div class="text-muted small">
            @if($order->customer?->insurance_fee)
                Insurance: ${{ number_format($order->customer->insurance_fee, 2) }}<br>
            @endif
            @if($order->customs_description)
                Customs Description: {{ $order->customs_description }}<br>
            @endif
            Processed: {{ $order->date_purchased?->format('g:ia M jS, Y') }}<br>
            @if($creator)
                Created By: {{ $creator }}
            @endif
        </div>
    </div>
</div>

{{-- From / To / Comments --}}
<div class="row mb-4">
    <div class="col-md-4">
        <h5 class="fw-semibold">From:</h5>
        <div class="border rounded p-3 small">
            {{ $order->customers_name }}<br>
            @if($order->customer?->billing_id)
                Attn: {{ $order->customer->billing_id }}<br>
            @endif
            {{ $order->customers_street_address }}<br>
            @if($order->customers_suburb) {{ $order->customers_suburb }}<br> @endif
            {{ $order->customers_city }}, {{ $order->customers_state }} {{ $order->customers_postcode }}<br>
            {{ $order->customers_country }}
        </div>
    </div>
    <div class="col-md-4">
        <h5 class="fw-semibold">To:</h5>
        <div class="border rounded p-3 small">
            {{ $order->delivery_name }}<br>
            @if($order->delivery_company) {{ $order->delivery_company }}<br> @endif
            {{ $order->delivery_street_address }}<br>
            @if($order->delivery_suburb) {{ $order->delivery_suburb }}<br> @endif
            {{ $order->delivery_city }}, {{ $order->delivery_state }} {{ $order->delivery_postcode }}<br>
            {{ $order->delivery_country }}
        </div>
    </div>
    <div class="col-md-4">
        <h5 class="fw-semibold">Comments</h5>
        <div class="small">{{ $order->comments ?: 'None' }}</div>
    </div>
</div>

{{-- Billing section --}}
<h5 class="fw-semibold">Billing:</h5>
<hr class="mt-1 mb-3">
<div class="row mb-4">
    <div class="col-md-4">
        <h6 class="fw-semibold text-muted">Address:</h6>
        <div class="small">
            {{ $order->billing_name }}<br>
            @if($order->billing_company) {{ $order->billing_company }}<br> @endif
            {{ $order->billing_street_address }}<br>
            @if($order->billing_suburb) {{ $order->billing_suburb }}<br> @endif
            {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postcode }}<br>
            {{ $order->billing_country }}
        </div>
    </div>
    <div class="col-md-4">
        <h6 class="fw-semibold text-muted">Card:</h6>
        <div class="small">
            @if($order->customer)
                Name: {{ $order->customer->cc_owner ?: $order->customer->full_name }}<br>
                {{ $order->customer->masked_cc_number ?: 'No card on file' }}<br>
                @if($order->customer->cc_expires)
                    Expires: {{ $order->customer->cc_expires }}
                @endif
            @else
                No customer data
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <h6 class="fw-semibold text-muted">Charges:</h6>
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
        <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/charge" class="btn btn-sm btn-outline-success mt-2">
            <i data-lucide="credit-card" class="icon--sm me-1"></i>Charge / Edit Totals
        </a>
    </div>
</div>

{{-- Status History --}}
<h5 class="fw-semibold">Status History</h5>
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

{{-- Delete order (at bottom, less prominent) --}}
<div class="mt-3 mb-4">
    <a href="/{{ $prefix }}/orders/delete/{{ $order->orders_id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this order? This cannot be undone.')">
        <i data-lucide="trash-2" class="icon--sm me-1"></i>Delete Order
    </a>
</div>
@endsection
