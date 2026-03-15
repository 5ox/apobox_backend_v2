@extends('layouts.manager')
@section('title', 'USPS Postage Calculator - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="USPS Postage Calculator" subtitle="Compare retail vs corporate rates" />

<div class="row">
    {{-- Left: Input form --}}
    <div class="col-lg-5">
        <x-detail-card title="Package Details">
            <form method="GET" action="{{ route($prefix . '.tools.postage-calculator') }}">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Destination ZIP Code</label>
                    <input type="text" name="zip" class="form-control" placeholder="e.g. 10001"
                        value="{{ request('zip') }}" required pattern="\d{5}" maxlength="5">
                    <div class="form-text">Origin: {{ $originZip }} (Plymouth, IN)</div>
                </div>

                <label class="form-label fw-semibold">Weight</label>
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="input-group">
                            <input type="number" name="pounds" class="form-control" placeholder="0"
                                value="{{ request('pounds', 0) }}" min="0" max="70">
                            <span class="input-group-text">lb</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <input type="number" name="ounces" class="form-control" placeholder="0"
                                value="{{ request('ounces', 0) }}" min="0" max="15">
                            <span class="input-group-text">oz</span>
                        </div>
                    </div>
                </div>

                <label class="form-label fw-semibold">Dimensions</label>
                <div class="row mb-3">
                    <div class="col-4">
                        <div class="input-group input-group-sm">
                            <input type="number" name="length" class="form-control" placeholder="L"
                                value="{{ request('length') }}" min="0" step="0.1" required>
                            <span class="input-group-text">in</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group input-group-sm">
                            <input type="number" name="width" class="form-control" placeholder="W"
                                value="{{ request('width') }}" min="0" step="0.1" required>
                            <span class="input-group-text">in</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group input-group-sm">
                            <input type="number" name="height" class="form-control" placeholder="H"
                                value="{{ request('height') }}" min="0" step="0.1" required>
                            <span class="input-group-text">in</span>
                        </div>
                    </div>
                </div>
                <div class="rounded-3 border bg-light-subtle p-3 mb-3">
                    <div class="fw-semibold mb-2">How to measure</div>
                    <img
                        src="{{ asset('images/usps-package-measure-guide.svg') }}"
                        alt="Package measurement guide showing length as the longest side, width across the front, and height from top to bottom."
                        class="img-fluid rounded-2 border bg-white mb-2"
                    >
                    <div class="form-text mb-0">Measure the outside of the sealed box. Length is the longest side.</div>
                </div>
                <div class="form-text mb-3">USPS pricing requires length, width, and height.</div>

                <button type="submit" class="btn btn-primary w-100">
                    <i data-lucide="calculator" class="icon--sm me-1"></i>Calculate Rates
                </button>
            </form>
        </x-detail-card>

        <a href="{{ route($prefix . '.tools.index') }}" class="btn btn-sm btn-outline-secondary">
            <i data-lucide="arrow-left" class="icon--sm me-1"></i>Back to Tools
        </a>
    </div>

    {{-- Right: Results --}}
    <div class="col-lg-7">
        @if($error)
            <div class="alert alert-danger">
                <i data-lucide="alert-circle" class="icon--sm me-1"></i>
                USPS rate lookup error: {{ $error }}
            </div>
        @elseif($rates !== null && count($rates) > 0)
            <x-table-card title="Rate Comparison">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th class="text-end">Retail Rate</th>
                            <th class="text-end">Our Rate</th>
                            <th class="text-end">Savings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rates as $rate)
                            @php
                                $ourRate = $rate['rate'];
                                $retailRate = $rate['retail_rate'] ?? null;
                                $rateSource = $rate['rate_source'] ?? 'RETAIL';
                                $savings = ($retailRate && $retailRate > $ourRate) ? $retailRate - $ourRate : null;
                                $pct = ($savings && $retailRate > 0) ? round(($savings / $retailRate) * 100) : null;
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $rate['label'] ?? $rate['service'] }}</span>
                                    <span class="text-muted small">({{ $rate['rateIndicator'] ?? '?' }})</span>
                                    <br>
                                    <small class="{{ $rateSource === 'COMMERCIAL' ? 'text-success' : 'text-warning' }}">
                                        {{ $rateSource === 'COMMERCIAL' ? 'Commercial rate loaded' : 'Retail fallback' }}
                                    </small>
                                    @if(!empty($rate['fees']))
                                        <br>
                                        @foreach($rate['fees'] as $fee)
                                            <small class="text-warning">+ {{ $fee['name'] }}: ${{ number_format($fee['price'], 2) }}</small>
                                            @if(!$loop->last) <br> @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-end text-muted">
                                    {{ $retailRate ? '$' . number_format($retailRate, 2) : '—' }}
                                </td>
                                <td class="text-end fw-semibold">
                                    ${{ number_format($ourRate, 2) }}
                                    @if($rateSource !== 'COMMERCIAL' && !empty($rate['commercial_error']))
                                        <br><small class="text-muted">{{ $rate['commercial_error'] }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($savings)
                                        <span class="text-success fw-semibold">-${{ number_format($savings, 2) }}</span>
                                        <br><small class="text-muted">{{ $pct }}% off</small>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-card>
        @elseif($rates !== null && count($rates) === 0)
            <div class="alert alert-warning">
                <i data-lucide="alert-triangle" class="icon--sm me-1"></i>
                No rates found for the given parameters. Check your weight and ZIP code.
            </div>
        @else
            <div class="card border-dashed">
                <div class="card-body text-center py-5 text-muted">
                    <i data-lucide="package" style="width:48px;height:48px;" class="mb-3 opacity-50"></i>
                    <p class="mb-0">Enter package details and click <strong>Calculate Rates</strong> to see a side-by-side comparison.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
