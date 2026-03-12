@extends('layouts.manager')
@section('title', 'Charge Order #' . $order->orders_id . ' - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<x-page-header title="Charge Order #{{ $order->orders_id }}" subtitle="{{ $order->customer?->full_name }}" />

@if(!$allowCharge['allow'])
    <div class="alert alert-warning"><i data-lucide="alert-triangle" class="icon--sm me-1"></i>{{ $allowCharge['message'] }}</div>
@endif

<x-table-card title="Charge Summary">
    <table class="table table-modern">
        <tbody>
            @if($order->shipping)<tr><td>Shipping</td><td class="text-end">${{ number_format($order->shipping->value, 2) }}</td></tr>@endif
            @if($order->insurance)<tr><td>Insurance</td><td class="text-end">${{ number_format($order->insurance->value, 2) }}</td></tr>@endif
            @if($order->battery)<tr><td>Battery</td><td class="text-end">${{ number_format($order->battery->value, 2) }}</td></tr>@endif
            @if($order->repack)<tr><td>Repack</td><td class="text-end">${{ number_format($order->repack->value, 2) }}</td></tr>@endif
            @if($order->storage)<tr><td>Storage</td><td class="text-end">${{ number_format($order->storage->value, 2) }}</td></tr>@endif
            @if($order->fee)<tr><td>Fee</td><td class="text-end">${{ number_format($order->fee->value, 2) }}</td></tr>@endif
            @if($order->subtotal)<tr class="fw-bold"><td>Subtotal</td><td class="text-end">${{ number_format($order->subtotal->value, 2) }}</td></tr>@endif
            @if($order->total)<tr class="fw-bold"><td>Total</td><td class="text-end">${{ number_format($order->total->value, 2) }}</td></tr>@endif
        </tbody>
    </table>
</x-table-card>

<div class="action-bar mt-3">
    @if($allowCharge['allow'])
        <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/charge?confirm=1" class="btn btn-success" onclick="return confirm('Charge ${{ number_format($order->total?->value ?? 0, 2) }}?')"><i data-lucide="credit-card" class="icon--sm me-1"></i>Confirm Charge</a>
    @endif
    <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}" class="btn btn-secondary"><i data-lucide="arrow-left" class="icon--sm me-1"></i>Back</a>
</div>
@endsection
