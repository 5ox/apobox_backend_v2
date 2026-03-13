@extends('layouts.manager')
@section('title', 'New Order - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

{{-- Customer Header (matches customer/view design) --}}
<div class="d-flex flex-wrap align-items-center gap-3 mb-3">
    @if($customer->billing_id)
        <span class="badge bg-primary fs-5 px-4 py-2" style="border-radius:12px">{{ $customer->billing_id }}</span>
    @endif
    <h2 class="mb-0">{{ $customer->full_name }}</h2>
    <span class="text-muted">&mdash; New Order</span>
</div>

{{-- Alerts: open tickets & custom requests --}}
@if(!empty($openTickets))
    <div class="alert alert-warning d-flex align-items-start gap-2 py-2">
        <i data-lucide="message-circle" class="icon mt-1 flex-shrink-0"></i>
        <div>
            <strong>{{ count($openTickets) }} open support {{ Str::plural('ticket', count($openTickets)) }}:</strong>
            @foreach($openTickets as $ticket)
                <a href="{{ $ticket['url'] }}" target="_blank" class="ms-2 text-decoration-none">
                    #{{ $ticket['id'] }} <span class="text-muted small">({{ $ticket['status'] }})</span>
                </a>{{ !$loop->last ? ',' : '' }}
            @endforeach
        </div>
    </div>
@endif
@if($requests->isNotEmpty())
    <div class="alert alert-info d-flex align-items-start gap-2 py-2">
        <i data-lucide="package" class="icon mt-1 flex-shrink-0"></i>
        <div>
            <strong>{{ $requests->count() }} pending custom {{ Str::plural('request', $requests->count()) }}:</strong>
            @foreach($requests as $req)
                <span class="ms-2">#{{ $req->custom_orders_id }} &mdash; {{ Str::limit($req->instructions, 40) }}</span>{{ !$loop->last ? ',' : '' }}
            @endforeach
        </div>
    </div>
@endif

<form method="POST" action="/{{ $prefix }}/orders/add/{{ $customer->customers_id }}" id="addOrderForm">
    @csrf
    <div class="row g-4">
        {{-- Left Column --}}
        <div class="col-lg-6">
            <x-form-section title="Tracking">
                <div class="mb-3">
                    <label class="form-label">Inbound Tracking Number</label>
                    <div class="input-group">
                        <input type="text" name="inbound_tracking" id="inbound-tracking" class="form-control"
                               value="{{ old('inbound_tracking') }}" placeholder="Scan or paste tracking number" autofocus>
                        <span class="input-group-text" id="carrier-badge">
                            <span class="text-muted small">Auto-detect</span>
                        </span>
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="Package Details">
                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <label class="form-label">Length (in)</label>
                        <input type="number" step="0.01" min="0" name="length" class="form-control"
                               value="{{ old('length') }}">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Width (in)</label>
                        <input type="number" step="0.01" min="0" name="width" class="form-control"
                               value="{{ old('width') }}">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Depth (in)</label>
                        <input type="number" step="0.01" min="0" name="depth" class="form-control"
                               value="{{ old('depth') }}">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Pounds</label>
                        <input type="number" step="1" min="0" name="weight_lb" id="weight-lb" class="form-control"
                               value="{{ old('weight_lb') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Ounces</label>
                        <input type="number" step="0.1" min="0" name="weight_oz" id="weight-oz" class="form-control"
                               value="{{ old('weight_oz') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Customs Description</label>
                    <select name="customs_description" class="form-select">
                        @foreach([
                            'Household & Personal Goods',
                            'Clothing & Apparel',
                            'Electronics & Accessories',
                            'Health & Beauty Products',
                            'Books & Documents',
                            'Food & Supplements',
                        ] as $desc)
                            <option value="{{ $desc }}" @selected(old('customs_description', 'Household & Personal Goods') === $desc)>{{ $desc }}</option>
                        @endforeach
                    </select>
                </div>
            </x-form-section>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-6">
            <x-form-section title="Shipping">
                <div class="mb-3">
                    <label class="form-label">Delivery Address</label>
                    <select name="address_id" class="form-select">
                        @foreach($customersAddresses as $id => $name)
                            <option value="{{ $id }}" @selected(old('address_id') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Mail Class</label>
                        <select name="mail_class" class="form-select">
                            @foreach($mailClasses as $value => $label)
                                <option value="{{ $value }}" @selected(old('mail_class', $defaultMailClass) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Insurance Coverage ($)</label>
                        <input type="number" step="0.01" min="0" name="insurance_coverage" class="form-control"
                               value="{{ old('insurance_coverage', number_format($customer->insurance_amount ?? 0, 2, '.', '')) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="orders_status" class="form-select">
                        @foreach($orderStatuses as $status)
                            <option value="{{ $status->orders_status_id }}" @selected(old('orders_status', 1) == $status->orders_status_id)>{{ $status->orders_status_name }}</option>
                        @endforeach
                    </select>
                </div>
            </x-form-section>

            <x-form-section title="Additional Fees">
                @php
                    $feeRates = config('apobox.orders.fee_rates', []);
                    $fees = [
                        'fee_inspection' => ['label' => 'Inspection', 'amount' => $feeRates['inspection'] ?? '5.00'],
                        'fee_return' => ['label' => 'Return', 'amount' => $feeRates['return'] ?? '10.00'],
                        'fee_misaddressed' => ['label' => 'Misaddressed', 'amount' => $feeRates['misaddressed'] ?? '5.00'],
                        'fee_ship_to_us' => ['label' => 'Ship to US', 'amount' => $feeRates['ship_to_us'] ?? '10.00'],
                    ];
                @endphp
                <div class="row g-2">
                    @foreach($fees as $name => $fee)
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" @checked(old($name))>
                                <label class="form-check-label" for="{{ $name }}">{{ $fee['label'] }} <span class="text-muted small">${{ $fee['amount'] }}</span></label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-form-section>

            <x-form-section title="Additional">
                @if($requests->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">Link Custom Request</label>
                        <select name="custom_package_request_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($requests as $req)
                                <option value="{{ $req->custom_orders_id }}" @selected(old('custom_package_request_id') == $req->custom_orders_id)>
                                    #{{ $req->custom_orders_id }} &mdash; {{ Str::limit($req->instructions, 60) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Comments</label>
                    <textarea name="comments" class="form-control" rows="3">{{ old('comments') }}</textarea>
                </div>
            </x-form-section>
        </div>
    </div>

    <div class="action-bar mt-3">
        <button type="submit" class="btn btn-primary"><i data-lucide="plus" class="icon--sm me-1"></i>Create Order</button>
        <a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trackingInput = document.getElementById('inbound-tracking');
    const carrierBadge = document.getElementById('carrier-badge');
    if (!trackingInput || !carrierBadge) return;

    const carriers = [
        { name: 'UPS',    color: 'bg-warning text-dark', test: v => /^1Z[A-Z0-9]{16}$/i.test(v) },
        { name: 'FedEx',  color: 'bg-primary',           test: v => /^\d{12}$/.test(v) || /^\d{15}$/.test(v) || /^\d{20,22}$/.test(v) },
        { name: 'Amazon', color: 'bg-dark',              test: v => /^TBA/i.test(v) },
        { name: 'DHL',    color: 'bg-danger',            test: v => /^\d{10}$/.test(v) || /^[A-Z]{3}\d{7,}$/i.test(v) },
        { name: 'USPS',   color: 'bg-info text-dark',    test: v => /^(9[0-9]{15,21}|[A-Z]{2}\d{9}US)$/i.test(v) },
    ];

    trackingInput.addEventListener('input', function() {
        const val = this.value.trim();
        if (!val) {
            carrierBadge.innerHTML = '<span class="text-muted small">Auto-detect</span>';
            return;
        }
        const match = carriers.find(c => c.test(val));
        if (match) {
            carrierBadge.innerHTML = '<span class="badge ' + match.color + '">' + match.name + '</span>';
        } else {
            carrierBadge.innerHTML = '<span class="badge bg-secondary">Unknown</span>';
        }
    });

    if (trackingInput.value) trackingInput.dispatchEvent(new Event('input'));
});
</script>
@endpush
