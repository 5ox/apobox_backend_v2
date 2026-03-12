@extends('layouts.manager')
@section('title', 'Order #' . $order->orders_id . ' - APO Box Admin')
@section('content')
@php
    $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee';
    $packageMetrics = collect([
        ['short' => 'L', 'label' => 'Length', 'value' => $order->length],
        ['short' => 'W', 'label' => 'Width', 'value' => $order->width],
        ['short' => 'H', 'label' => 'Height', 'value' => $order->depth],
    ])->filter(static fn (array $dimension): bool => filled($dimension['value']))
        ->map(static fn (array $dimension): array => [
            ...$dimension,
            'display' => rtrim(rtrim(number_format((float) $dimension['value'], 2, '.', ''), '0'), '.'),
        ])->values();
    $dimensionHeadline = $packageMetrics->isNotEmpty()
        ? $packageMetrics->pluck('display')->implode(' × ') . ' in'
        : null;
    $weightDisplay = $order->weight ? $order->weight . ' lb' : 'N/A';
@endphp
<x-page-header title="Order #{{ $order->orders_id }}" subtitle="{{ $order->customer?->full_name }}" />

<div class="row">
    <div class="col-md-6">
        <x-detail-card title="Order Details">
            <x-detail-row label="Customer"><a href="/{{ $prefix }}/customers/view/{{ $order->customer?->customers_id }}">{{ $order->customer?->full_name }}</a></x-detail-row>
            <x-detail-row label="Status"><x-status-badge :status="$order->status?->orders_status_name" /></x-detail-row>
            <x-detail-row label="Date">{{ $order->date_purchased?->format('m/d/Y g:i A') }}</x-detail-row>
            <x-detail-row label="Mail Class">{{ $order->mail_class }}</x-detail-row>
            <x-detail-row label="Outbound">{{ $order->usps_track_num ?: 'N/A' }}</x-detail-row>
            <x-detail-row label="Inbound">{{ $order->inbound_tracking ?: 'N/A' }}</x-detail-row>
            <x-detail-row label="Dimensions" class="detail-card__row--package">
                @if($packageMetrics->isNotEmpty())
                    <div class="package-dimensions">
                        <div class="package-dimensions__visual" aria-hidden="true">
                            <div class="package-box">
                                <span class="package-box__face package-box__face--top"></span>
                                <span class="package-box__face package-box__face--side"></span>
                                <span class="package-box__face package-box__face--front"></span>
                                <span class="package-box__tape"></span>
                            </div>
                        </div>
                        <div class="package-dimensions__content">
                            <div class="package-dimensions__headline">{{ $dimensionHeadline }}</div>
                            <div class="package-dimensions__chips">
                                @foreach($packageMetrics as $metric)
                                    <span class="package-dimensions__chip" title="{{ $metric['label'] }}">
                                        <span class="package-dimensions__chip-label">{{ $metric['short'] }}</span>
                                        <span class="package-dimensions__chip-value">{{ $metric['display'] }} in</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <span class="text-muted">N/A</span>
                @endif
            </x-detail-row>
            <x-detail-row label="Weight">{{ $weightDisplay }}</x-detail-row>
            @if($creator)<x-detail-row label="Created by">{{ $creator }}</x-detail-row>@endif
        </x-detail-card>
    </div>
    <div class="col-md-6">
        <x-form-section title="Update Status">
            <form method="POST" action="/{{ $prefix }}/orders/{{ $order->orders_id }}/update-status">
                @csrf
                <div class="mb-2">
                    <select name="orders_status" class="form-select form-select-sm">
                        @foreach($ordersStatuses as $id => $name)
                            <option value="{{ $id }}" @selected($order->orders_status == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2"><textarea name="status_history_comments" class="form-control form-control-sm" rows="2" placeholder="Comments"></textarea></div>
                <div class="mb-2"><input type="text" name="usps_track_num" class="form-control form-control-sm" placeholder="Outbound tracking number" value="{{ $order->usps_track_num }}"></div>
                <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="notify_customer" id="notifyCustomer" value="1" checked><label class="form-check-label" for="notifyCustomer">Notify Customer</label></div>
                <button type="submit" class="btn btn-sm btn-primary"><i data-lucide="check" class="icon--sm me-1"></i>Update Status</button>
            </form>
        </x-form-section>

        <div class="action-bar mt-3">
            <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/charge" class="btn btn-sm btn-outline-success"><i data-lucide="credit-card" class="icon--sm me-1"></i>Charge</a>
            @if($mailClass === 'usps')
                <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/print_label" class="btn btn-sm btn-outline-secondary"><i data-lucide="printer" class="icon--sm me-1"></i>Print USPS Label</a>
            @else
                <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary"><i data-lucide="printer" class="icon--sm me-1"></i>{{ $action }} FedEx Label</a>
            @endif
            <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/delete_label" class="btn btn-sm btn-outline-warning" onclick="return confirm('Delete label?')"><i data-lucide="x" class="icon--sm me-1"></i>Delete Label</a>
            <a href="/{{ $prefix }}/orders/delete/{{ $order->orders_id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this order?')"><i data-lucide="trash-2" class="icon--sm me-1"></i>Delete Order</a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <x-table-card title="Order Charges">
            <table class="table table-modern">
                <tbody>
                    @foreach($orderCharges as $charge)
                        <tr @if(in_array($charge->class, ['ot_subtotal', 'ot_total'])) class="fw-bold" @endif>
                            <td>{{ $charge->title }}</td>
                            <td class="text-end">${{ number_format($charge->value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-table-card>
    </div>
    <div class="col-md-6">
        <x-table-card title="Status History">
            <table class="table table-modern">
                <thead><tr><th>Date</th><th>Status</th><th>Comments</th></tr></thead>
                <tbody>
                    @foreach($statusHistories as $history)
                        <tr>
                            <td>{{ $history->date_added?->format('m/d/Y g:i A') }}</td>
                            <td>{{ $history->status?->orders_status_name }}</td>
                            <td>{{ $history->comments }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-table-card>
    </div>
</div>
@endsection
