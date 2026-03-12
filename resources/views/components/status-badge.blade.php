@props(['status'])
@php
    $statusMap = [
        'Warehouse'         => 'warehouse',
        'Awaiting Payment'  => 'awaiting-payment',
        'Shipped'           => 'shipped',
        'Paid'              => 'paid',
        'Returned'          => 'returned',
        'Awaiting Package'  => 'awaiting-package',
    ];
    $slug = $statusMap[$status] ?? \Illuminate\Support\Str::slug($status ?? 'unknown');
@endphp
<span {{ $attributes->merge(['class' => "status-badge status-badge--{$slug}"]) }}>{{ $status }}</span>
