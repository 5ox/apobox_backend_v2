@extends('layouts.manager')
@section('title', 'Dashboard - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

{{-- Search & Quick Order --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <form action="/{{ $prefix }}" method="GET" class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search by Order #, Customer, Billing ID, or Scan (S:...)" autofocus>
            <button type="submit" class="btn btn-outline-primary fw-semibold px-4">SEARCH</button>
        </form>
    </div>
    <div class="col-md-4">
        <form action="{{ route($prefix . '.customers.quick-order') }}" method="GET" class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Quick Order by ID">
            <button type="submit" class="btn btn-outline-primary fw-semibold px-4">ADD</button>
        </form>
    </div>
</div>

{{-- Employee Activity --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <i data-lucide="package" class="text-primary" style="width:18px;height:18px"></i>
                <span class="fw-semibold">Employee Activity</span>
                <span class="badge bg-primary rounded-pill">{{ $statsTotal }}</span>
            </div>
            <div class="vr d-none d-sm-block"></div>
            {{-- Per-employee totals --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($employeeTotals->isNotEmpty())
                    @foreach($employeeTotals as $id => $total)
                        @php $isTop = $loop->first && $employeeTotals->count() > 1; @endphp
                        <span class="small {{ $isTop ? 'fw-semibold' : '' }}">
                            @if($isTop)<i data-lucide="trophy" class="text-warning" style="width:14px;height:14px;vertical-align:-2px"></i>@endif
                            {{ $employeeNames[$id] }}
                            <span class="badge {{ $isTop ? 'bg-warning text-dark' : 'bg-secondary' }} rounded-pill">{{ $total }}</span>
                        </span>
                    @endforeach
                @else
                    <span class="text-muted small">No packages yet</span>
                @endif
            </div>
            <div class="ms-auto">
                <div class="btn-group btn-group-sm" role="group">
                    @foreach(['7d' => '7D', '30d' => '30D', '90d' => '90D', '12m' => '12M'] as $val => $label)
                        <a href="?stats={{ $val }}" class="btn btn-outline-primary btn-sm {{ $statsRange === $val ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </div>
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
                        <th>Inbound Tracking</th>
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
                        // Determine inbound tracking number and carrier
                        $inboundTrack = '';
                        $inboundCarrier = '';
                        $inboundUrl = '';
                        if ($order->usps_track_num_in) {
                            $inboundTrack = $order->usps_track_num_in;
                            $inboundCarrier = 'USPS';
                            $inboundUrl = 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . $inboundTrack;
                        } elseif ($order->ups_track_num) {
                            $inboundTrack = $order->ups_track_num;
                            $inboundCarrier = 'UPS';
                            $inboundUrl = 'https://www.ups.com/track?tracknum=' . $inboundTrack;
                        } elseif ($order->fedex_track_num) {
                            $inboundTrack = $order->fedex_track_num;
                            $inboundCarrier = 'FedEx';
                            $inboundUrl = 'https://www.fedex.com/fedextrack/?trknbr=' . $inboundTrack;
                        } elseif ($order->dhl_track_num) {
                            $inboundTrack = $order->dhl_track_num;
                            $inboundCarrier = 'DHL';
                            $inboundUrl = 'https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id=' . $inboundTrack;
                        }
                    @endphp
                    <tr>
                        <td>
                            <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}" class="fw-semibold">{{ $order->orders_id }}</a>
                            @if($order->problem_reason)
                                <span class="badge bg-danger small">{{ $order->problem_reason }}</span>
                            @endif
                        </td>
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
                        <td class="small text-nowrap" title="{{ $inboundTrack }}">
                            @if($inboundTrack)
                                <a href="{{ $inboundUrl }}" target="_blank" class="text-decoration-none">
                                    <span class="badge bg-light text-dark border me-1">{{ $inboundCarrier }}</span>...{{ substr($inboundTrack, -7) }}
                                </a>
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
