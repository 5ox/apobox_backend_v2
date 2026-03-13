@extends('layouts.manager')
@section('title', 'Charge Order #' . $order->orders_id . ' - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<x-page-header title="Charge Order #{{ $order->orders_id }}" subtitle="{{ $order->customer?->full_name }}" />

@if(!$allowCharge['allow'])
    <div class="alert alert-warning"><i data-lucide="alert-triangle" class="icon--sm me-1"></i>{{ $allowCharge['message'] }}</div>
@endif

<div class="row">
    {{-- Left column: Order Info --}}
    <div class="col-md-5">
        <x-detail-card title="Order Info">
            <x-detail-row label="Customer"><a href="/{{ $prefix }}/customers/view/{{ $order->customer?->customers_id }}">{{ $order->customer?->full_name }}</a></x-detail-row>
            <x-detail-row label="Status"><x-status-badge :status="$order->status?->orders_status_name" /></x-detail-row>
            <x-detail-row label="Mail Class">{{ $order->mail_class ?: 'N/A' }}</x-detail-row>
            <x-detail-row label="Weight">{{ $order->weight ? $order->weight . ' lb' : 'N/A' }} ({{ (int)($order->weight_oz ?? 0) }} oz)</x-detail-row>
            <x-detail-row label="Dimensions">{{ $order->dimensions ?: 'N/A' }}</x-detail-row>
            <x-detail-row label="Ship to">{{ $order->delivery_city }}, {{ $order->delivery_state }} {{ $order->delivery_postcode }}</x-detail-row>
            <x-detail-row label="Payment">
                @if($invoiceCustomer)
                    <span class="badge bg-info">Invoice Customer</span>
                @elseif($order->customer?->card_token)
                    <span class="badge bg-success">Card on File</span>
                @else
                    <span class="badge bg-danger">No Payment Method</span>
                @endif
            </x-detail-row>
        </x-detail-card>

        {{-- USPS Rate Lookup Results --}}
        @if(!empty($uspsRates) && !isset($uspsRates['error']))
            <x-detail-card title="USPS Rate Lookup">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Service</th><th class="text-end">Our Rate</th><th class="text-end">Retail</th><th class="text-end">Savings</th></tr></thead>
                    <tbody>
                        @foreach($uspsRates as $rate)
                            @php
                                $ourRate = $rate['rate'];
                                $retailRate = $rate['retail_rate'] ?? null;
                                $savings = ($retailRate && $retailRate > $ourRate) ? $retailRate - $ourRate : null;
                            @endphp
                            <tr @if($autoRate && ($rate['class_id'] ?? '') === ($autoRate['class_id'] ?? '')) class="table-success" @endif>
                                <td>{{ $rate['description'] ?: $rate['service'] }}</td>
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
        @elseif(isset($uspsRates['error']))
            <div class="alert alert-danger mt-2"><i data-lucide="alert-circle" class="icon--sm me-1"></i>USPS rate lookup error: {{ $uspsRates['error'] }}</div>
        @elseif(empty($order->weight_oz) || empty($order->delivery_postcode))
            <div class="alert alert-warning mt-2"><i data-lucide="alert-triangle" class="icon--sm me-1"></i>Cannot look up USPS rates: missing weight or delivery ZIP code.</div>
        @endif
    </div>

    {{-- Right column: Editable Charge Form --}}
    <div class="col-md-7">
        <form method="POST" action="/{{ $prefix }}/orders/{{ $order->orders_id }}/charge" id="chargeForm">
            @csrf
            <x-table-card title="Charge Summary">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr><th>Line Item</th><th class="text-end" style="width:140px">Amount</th><th style="width:100px"></th></tr>
                    </thead>
                    <tbody>
                        @php
                            $lineItems = [
                                ['name' => 'OrderShipping', 'label' => 'Shipping', 'relation' => 'shipping', 'auto' => $autoRate ? ($autoRate['retail_rate'] ?? $autoRate['rate']) : null, 'hint' => $autoRate ? ($autoRate['description'] ?: $autoRate['service']) . ' (retail)' : null],
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
                                    <td>
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
                                    <td class="text-center">
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
                        <tr class="fw-bold">
                            <td>Subtotal</td>
                            <td class="text-end" id="subtotalDisplay">${{ number_format($order->subtotal?->value ?? 0, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr class="fw-bold fs-5">
                            <td>Total</td>
                            <td class="text-end" id="totalDisplay">${{ number_format($order->total?->value ?? 0, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </x-table-card>

            <div class="action-bar mt-3">
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
                <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="icon--sm me-1"></i>Back
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('chargeForm');
    if (!form) return;

    const inputs = form.querySelectorAll('.line-item-input');
    const subtotalEl = document.getElementById('subtotalDisplay');
    const totalEl = document.getElementById('totalDisplay');

    function recalculate() {
        let sum = 0;
        inputs.forEach(function(input) {
            sum += parseFloat(input.value) || 0;
        });
        const formatted = '$' + sum.toFixed(2);
        subtotalEl.textContent = formatted;
        totalEl.textContent = formatted;
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
