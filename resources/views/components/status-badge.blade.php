@props(['status'])
@php
    $statusMap = [
        'Warehouse'         => 'warehouse',
        'Awaiting Payment'  => 'awaiting-payment',
        'Shipped'           => 'shipped',
        'Paid'              => 'paid',
        'Returned'          => 'returned',
        'Problem'           => 'problem',
        'Awaiting Package'  => 'awaiting-package',
        'New'               => 'new',
        'Processing'        => 'processing',
        'Completed'         => 'completed',
        'Cancelled'         => 'cancelled',
    ];
    $slug = $statusMap[$status] ?? \Illuminate\Support\Str::slug($status ?? 'unknown');
@endphp
<span {{ $attributes->merge(['class' => "status-badge status-badge--{$slug}"]) }}>{{ $status }}</span>
