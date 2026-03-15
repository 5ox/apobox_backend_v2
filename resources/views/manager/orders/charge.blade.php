@extends('layouts.manager')
@section('title', 'Charge Order #' . $order->orders_id . ' - APO Box Admin')
@section('content')
@php
    $prefix = auth('admin')->user()->routePrefix();
    $weightOz = (int)($order->weight_oz ?? 0);
    $lbs = intdiv($weightOz, 16);
    $oz = $weightOz % 16;
@endphp

<x-page-header title="Charge Order #{{ $order->orders_id }}">
    <x-slot:actions>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="/{{ $prefix }}/customers/view/{{ $order->customer?->customers_id }}" class="app-tag app-tag--muted">
                <i data-lucide="user" class="icon--xs me-1"></i>{{ $order->customer?->full_name }}
            </a>
            <x-status-badge :status="$order->status?->orders_status_name" />
            <span class="app-tag app-tag--muted">{{ $order->mail_class ?: 'No class' }}</span>
            <span class="app-tag app-tag--muted">{{ $lbs }}lb {{ $oz }}oz</span>
            <span class="app-tag app-tag--muted">→ {{ $order->delivery_city }}, {{ $order->delivery_state }} {{ $order->delivery_postcode }}</span>
            @if($invoiceCustomer)
                <span class="app-tag app-tag--info">Invoice</span>
            @elseif($order->customer?->card_token)
                <span class="app-tag app-tag--success">Card on File</span>
            @else
                <span class="app-tag app-tag--danger">No Payment</span>
            @endif
        </div>
    </x-slot:actions>
</x-page-header>

@if(!$allowCharge['allow'])
    <div class="alert alert-warning py-2"><i data-lucide="alert-triangle" class="icon--sm me-1"></i>{{ $allowCharge['message'] }}</div>
@endif

<div class="row">
    {{-- Left column: Charge Summary (primary) --}}
    <div class="col-lg-7">
        <form method="POST" action="/{{ $prefix }}/orders/{{ $order->orders_id }}/charge" id="chargeForm">
            @csrf
            <x-table-card title="Charge Summary">
                <table class="table table-modern table-sm mb-0">
                    <thead>
                        <tr><th>Line Item</th><th class="text-end" style="width:130px">Amount</th><th style="width:50px"></th></tr>
                    </thead>
                    <tbody>
                        @php
                            $lineItems = [
                                ['name' => 'OrderShipping', 'label' => 'Shipping', 'relation' => 'shipping', 'auto' => $autoRate ? ($autoRate['retail_rate'] ?? $autoRate['rate']) : null, 'hint' => $autoRate ? ($autoRate['label'] ?? $autoRate['service']) . ' (' . ($autoRate['rateIndicator'] ?? 'SP') . ') retail' : null],
                                ['name' => 'OrderFee', 'label' => 'Handling Fee', 'relation' => 'fee', 'auto' => $autoFee ?? null, 'hint' => null],
                                ['name' => 'OrderInsurance', 'label' => 'Insurance', 'relation' => 'insurance', 'auto' => $autoInsurance ?? null, 'hint' => null],
                                ['name' => 'OrderStorage', 'label' => 'Storage', 'relation' => 'storage', 'auto' => null, 'hint' => null],
                                ['name' => 'OrderRepack', 'label' => 'Repack', 'relation' => 'repack', 'auto' => null, 'hint' => null],
                                ['name' => 'OrderBattery', 'label' => 'Inspection', 'relation' => 'inspection', 'auto' => $feeRates['inspection'] ?? null, 'hint' => null],
                                ['name' => 'OrderReturn', 'label' => 'Return', 'relation' => 'returnItem', 'auto' => $feeRates['return'] ?? null, 'hint' => null],
                                ['name' => 'OrderMisaddressed', 'label' => 'Misaddressed', 'relation' => 'misaddressed', 'auto' => $feeRates['misaddressed'] ?? null, 'hint' => null],
                                ['name' => 'OrderShipToUS', 'label' => 'Ship to US', 'relation' => 'shipToUS', 'auto' => $feeRates['ship_to_us'] ?? null, 'hint' => null],
                            ];
                        @endphp
                        @foreach($lineItems as $item)
                            @php $lineItem = $order->{$item['relation']}; @endphp
                            @if($lineItem)
                                <tr>
                                    <td class="align-middle">
                                        {{ $item['label'] }}
                                        @if($item['hint'])
                                            <br><small class="text-muted">{{ $item['hint'] }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" min="0" name="{{ $item['name'] }}[value]"
                                                value="{{ number_format($lineItem->value, 2, '.', '') }}"
                                                class="form-control form-control-sm text-end line-item-input"
                                                @if(!$allowCharge['allow']) disabled @endif>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($item['auto'] && $lineItem->value == 0 && $allowCharge['allow'])
                                            <button type="button" class="btn btn-outline-primary btn-sm auto-fill-btn"
                                                data-value="{{ number_format((float)$item['auto'], 2, '.', '') }}"
                                                data-target="{{ $item['name'] }}[value]"
                                                title="Auto-fill ${{ number_format((float)$item['auto'], 2) }}">
                                                <i data-lucide="zap" class="icon--xs"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold border-top">
                            <td>Total</td>
                            <td class="text-end fs-5" id="totalDisplay">${{ number_format($order->total?->value ?? 0, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </x-table-card>

            <div class="d-flex gap-2 mb-4">
                @if($allowCharge['allow'])
                    <button type="submit" name="submit" value="save" class="btn btn-primary">
                        <i data-lucide="save" class="icon--sm me-1"></i>Save Totals
                    </button>
                    <button type="submit" name="submit" value="charge" class="btn btn-success"
                        onclick="return confirm('Charge ' + document.getElementById('totalDisplay').textContent + ' to this customer?')">
                        <i data-lucide="credit-card" class="icon--sm me-1"></i>
                        {{ $invoiceCustomer ? 'Record Invoice' : 'Charge Card' }}
                    </button>
                @endif
                <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}" class="btn btn-outline-secondary">
                    <i data-lucide="arrow-left" class="icon--sm me-1"></i>Back
                </a>
            </div>
        </form>
    </div>

    {{-- Right column: USPS Rate Lookup --}}
    <div class="col-lg-5">
        @if(!empty($uspsRates))
            <x-detail-card title="Get Postage Rates">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Service</th><th class="text-end">Our Rate</th><th class="text-end">Retail</th><th class="text-end">Savings</th></tr></thead>
                    <tbody>
                        @foreach($uspsRates as $rate)
                            @php
                                $ourRate = $rate['rate'];
                                $retailRate = $rate['retail_rate'] ?? null;
                                $savings = ($retailRate && $retailRate > $ourRate) ? $retailRate - $ourRate : null;
                                $isSelected = $autoRate
                                    && $rate['service'] === $autoRate['service']
                                    && ($rate['rateIndicator'] ?? '') === ($autoRate['rateIndicator'] ?? '');
                            @endphp
                            <tr @if($isSelected) class="table-success" @endif>
                                <td>
                                    {{ $rate['label'] ?? $rate['service'] }}
                                    <span class="text-muted small">({{ $rate['rateIndicator'] ?? '?' }})</span>
                                    @if(!empty($rate['fees']))
                                        <br>
                                        @foreach($rate['fees'] as $fee)
                                            <small class="text-warning">+ {{ $fee['name'] }}: ${{ number_format($fee['price'], 2) }}</small>
                                            @if(!$loop->last) <br> @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-end">${{ number_format($ourRate, 2) }}</td>
                                <td class="text-end text-muted">{{ $retailRate ? '$' . number_format($retailRate, 2) : '—' }}</td>
                                <td class="text-end">
                                    @if($savings)
                                        <span class="text-success">-${{ number_format($savings, 2) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-detail-card>
        @endif

        @if($rateError)
            <div class="alert alert-danger py-2">
                <i data-lucide="alert-circle" class="icon--sm me-1"></i>{{ $rateError }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('chargeForm');
    if (!form) return;

    const inputs = form.querySelectorAll('.line-item-input');
    const totalEl = document.getElementById('totalDisplay');

    function recalculate() {
        let sum = 0;
        inputs.forEach(function(input) {
            sum += parseFloat(input.value) || 0;
        });
        totalEl.textContent = '$' + sum.toFixed(2);
    }

    inputs.forEach(function(input) {
        input.addEventListener('input', recalculate);
        input.addEventListener('change', recalculate);
    });

    // Auto-fill buttons
    form.querySelectorAll('.auto-fill-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetName = this.getAttribute('data-target');
            const value = this.getAttribute('data-value');
            const input = form.querySelector('[name="' + targetName + '"]');
            if (input) {
                input.value = value;
                input.dispatchEvent(new Event('input'));
                this.remove();
            }
        });
    });
});
</script>
@endpush
