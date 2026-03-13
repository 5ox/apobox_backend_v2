@extends('layouts.default')
@section('title', 'Order #' . $order->orders_id . ' - APO Box')
@section('content')
<x-page-header :title="'Order #' . $order->orders_id" />

<div class="row">
    <div class="col-md-6">
        <x-detail-card title="Order Details">
            <x-detail-row label="Status"><x-status-badge :status="$order->status?->orders_status_name" /></x-detail-row>
            <x-detail-row label="Date Purchased">{{ $order->date_purchased?->format('m/d/Y g:i A') }}</x-detail-row>
            <x-detail-row label="Date Shipped">{{ $order->date_shipped?->format('m/d/Y') ?? 'N/A' }}</x-detail-row>
            <x-detail-row label="Postage Class">{{ $order->mail_class }}</x-detail-row>
            <x-detail-row label="Outbound Tracking">{{ $order->usps_track_num ?: 'N/A' }}</x-detail-row>
            <x-detail-row label="Inbound Tracking">{{ $order->inbound_tracking ?: 'N/A' }}</x-detail-row>
            <x-detail-row label="Dimensions">{{ $order->dimensions }}</x-detail-row>
            <x-detail-row label="Weight">{{ $order->weight ? $order->weight . ' lb' : 'N/A' }}</x-detail-row>
        </x-detail-card>
    </div>
    <div class="col-md-6">
        <x-detail-card title="Order Charges">
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @foreach($orderCharges as $charge)
                            <tr @if(in_array($charge->class, ['ot_subtotal', 'ot_total'])) class="fw-bold" @endif>
                                <td>{{ $charge->title }}</td>
                                <td class="text-end">${{ number_format($charge->value, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($order->orders_status == 2)
                <div class="mt-3">
                    <a href="{{ url('/orders/' . $order->orders_id . '/pay') }}" class="btn btn-primary w-100">
                        <i data-lucide="credit-card" class="icon--sm"></i> Pay Now
                    </a>
                </div>
            @endif
        </x-detail-card>
    </div>
</div>

@if($order->statusHistory->isNotEmpty())
    <x-table-card title="Status History" class="mt-3">
        <table class="table table-modern">
            <thead><tr><th>Date</th><th>Status</th><th>Comments</th></tr></thead>
            <tbody>
                @foreach($order->statusHistory as $history)
                    <tr>
                        <td>{{ $history->date_added?->format('m/d/Y g:i A') }}</td>
                        <td>{{ $history->status?->orders_status_name }}</td>
                        <td>{{ $history->comments }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-table-card>
@endif

<div class="mt-3">
    <a href="{{ url('/orders') }}" class="btn btn-secondary"><i data-lucide="arrow-left" class="icon--sm"></i> Back to Orders</a>
</div>
@endsection
