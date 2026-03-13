@extends('layouts.manager')
@section('title', 'New Order - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="New Order" subtitle="Look up a customer to begin" />

<div class="row justify-content-center">
    <div class="col-lg-6">
        <x-form-section title="Customer Lookup">
            @if(!empty($error))
                <div class="alert alert-warning mb-3">
                    <i data-lucide="alert-triangle" class="icon--sm me-1"></i>{{ $error }}
                </div>
            @endif

            <form action="/{{ $prefix }}/orders/new" method="GET">
                <div class="mb-3">
                    <label class="form-label">Billing ID</label>
                    <div class="input-group input-group-lg">
                        <input type="text" name="q" class="form-control" placeholder="Scan or type Billing ID..." autofocus
                               value="{{ request('q') }}">
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i data-lucide="search" class="icon--sm me-1"></i>Find Customer
                        </button>
                    </div>
                    <div class="form-text">Enter the customer's Billing ID to start creating an order.</div>
                </div>
            </form>
        </x-form-section>
    </div>
</div>

@endsection
