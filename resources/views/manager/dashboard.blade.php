@extends('layouts.manager')
@section('title', 'Dashboard - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

{{-- Today's Stats Bar --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <i data-lucide="bar-chart-3" class="text-primary" style="width:20px;height:20px"></i>
                <span class="fw-semibold">Today's Packages</span>
                <span class="badge bg-primary rounded-pill fs-6">{{ $todayTotal }}</span>
            </div>
            @if($todayStats->isNotEmpty())
                <div class="d-flex flex-wrap align-items-center gap-3">
                    @foreach($todayStats as $i => $stat)
                        <div class="d-flex align-items-center gap-2 {{ $i === 0 && $todayStats->count() > 1 ? 'border rounded px-3 py-1 bg-warning bg-opacity-10' : '' }}">
                            @if($i === 0 && $todayStats->count() > 1)
                                <i data-lucide="trophy" class="text-warning" style="width:16px;height:16px"></i>
                            @else
                                <i data-lucide="user" class="text-muted" style="width:16px;height:16px"></i>
                            @endif
                            <span class="small {{ $i === 0 && $todayStats->count() > 1 ? 'fw-semibold' : '' }}">{{ $stat['name'] }}</span>
                            <span class="badge {{ $i === 0 && $todayStats->count() > 1 ? 'bg-warning text-dark' : 'bg-secondary' }} rounded-pill">{{ $stat['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <span class="text-muted small">No packages processed yet today</span>
            @endif
        </div>

        {{-- Last 10 Days Table --}}
        @if($dailyStats->sum('total') > 0)
        <hr class="my-3">
        <div class="table-responsive">
            <table class="table table-sm table-borderless mb-0 small align-middle">
                <thead>
                    <tr class="text-muted">
                        <th class="fw-semibold">Day</th>
                        @foreach($employeeNames as $name)
                            <th class="text-center fw-semibold">{{ $name }}</th>
                        @endforeach
                        <th class="text-center fw-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyStats->reverse() as $day)
                        <tr @if($day['date']->isToday()) class="table-active fw-semibold" @endif>
                            <td class="text-nowrap">{{ $day['label'] }}</td>
                            @foreach($employeeNames as $id => $name)
                                @php $cnt = $day['byEmployee'][$id] ?? 0; @endphp
                                <td class="text-center">
                                    @if($cnt > 0)
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">{{ $cnt }}</span>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center">
                                @if($day['total'] > 0)
                                    <span class="badge bg-dark rounded-pill">{{ $day['total'] }}</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@foreach([
    ['label' => 'Paid', 'orders' => $paid, 'status' => 'paid'],
    ['label' => 'Awaiting Payment', 'orders' => $awaitingPayment, 'status' => 'awaiting-payment'],
    ['label' => 'Warehouse', 'orders' => $inWarehouse, 'status' => 'warehouse'],
    ['label' => 'Problem', 'orders' => $problem, 'status' => 'problem'],
] as $section)
    @if($section['orders']->isNotEmpty())
    <div class="mb-4">
        <h2 class="mb-3"><x-status-badge :status="$section['label']" class="fs-6 px-3 py-2" /></h2>
        <div class="table-responsive">
            <table class="table table-modern table-sm align-middle">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>To</th>
                        <th>Dimensions</th>
                        <th>Weight</th>
                        <th>Class</th>
                        <th>Inbound</th>
                        <th>Outbound</th>
                        <th title="Comments"><i data-lucide="message-square" class="icon--sm"></i></th>
                        <th>Modified</th>
                        <th>Processed</th>
                        <th class="text-end">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($section['orders'] as $order)
                    @php
                        $dims = collect([$order->length, $order->width, $order->depth])
                            ->filter()
                            ->map(fn($v) => rtrim(rtrim(number_format((float)$v, 2), '0'), '.'));
                        $lbs = intdiv((int)($order->weight_oz ?? 0), 16);
                        $oz = (int)($order->weight_oz ?? 0) % 16;
                        $inbound = $order->usps_track_num_in ?: $order->ups_track_num ?: $order->fedex_track_num ?: $order->dhl_track_num ?: '';
                        $outbound = $order->usps_track_num ?: '';
                    @endphp
                    <tr>
                        <td><a href="/{{ $prefix }}/orders/{{ $order->orders_id }}" class="fw-semibold">{{ $order->orders_id }}</a></td>
                        <td>
                            @if($order->customer)
                                <a href="/{{ $prefix }}/customers/view/{{ $order->customer->customers_id }}">{{ $order->customer->billing_id }}</a>
                            @endif
                        </td>
                        <td class="text-nowrap small">
                            @if($dims->isNotEmpty())
                                {{ $dims->implode(' x ') }} in.
                            @endif
                        </td>
                        <td class="text-nowrap small">{{ $lbs }} lb, {{ $oz }} oz</td>
                        <td>{{ $order->mail_class }}</td>
                        <td class="small text-truncate" style="max-width:120px;" title="{{ $inbound }}">
                            @if($inbound)
                                <span class="text-muted">...{{ substr($inbound, -7) }}</span>
                            @endif
                        </td>
                        <td class="small text-truncate" style="max-width:120px;" title="{{ $outbound }}">
                            @if($outbound)
                                <span class="text-muted">...{{ substr($outbound, -7) }}</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($order->comments))
                                <i data-lucide="message-square" class="icon--sm text-warning" title="{{ Str::limit($order->comments, 80) }}"></i>
                            @endif
                        </td>
                        <td class="small text-nowrap">
                            @if($order->last_modified?->isToday()) Today
                            @elseif($order->last_modified) {{ $order->last_modified->format('n/j/y') }}
                            @endif
                        </td>
                        <td class="small text-nowrap">{{ $order->date_purchased?->format('n/j/y') }}</td>
                        <td class="text-end fw-semibold">${{ number_format($order->total?->value ?? 0, 2) }}</td>
                        <td class="text-nowrap">
                            <a href="mailto:{{ $order->customers_email_address }}" title="Email customer" class="text-muted me-1"><i data-lucide="mail" class="icon--sm"></i></a>
                            <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/charge" title="Charge" class="text-muted"><i data-lucide="credit-card" class="icon--sm"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endforeach

@if($paid->isEmpty() && $awaitingPayment->isEmpty() && $inWarehouse->isEmpty() && $problem->isEmpty())
    <div class="text-center py-5 text-muted">
        <i data-lucide="inbox" style="width:48px;height:48px" class="mb-3 opacity-50"></i>
        <p>No active orders</p>
    </div>
@endif
@endsection
