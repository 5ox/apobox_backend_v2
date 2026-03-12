@extends('layouts.manager')
@section('title', 'Edit Default Addresses - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Edit Default Addresses - {{ $customer->full_name }}</h2>
<form method="POST" action="/{{ $prefix }}/customers/{{ $customer->customers_id }}/edit/default-addresses">
    @csrf
    <div class="mb-3">
        <label class="form-label">Billing Address</label>
        <select name="customers_default_address_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($customersDefaultAddresses as $id => $name)
                <option value="{{ $id }}" @selected($customer->customers_default_address_id == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Shipping Address</label>
        <select name="customers_shipping_address_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($customersShippingAddresses as $id => $name)
                <option value="{{ $id }}" @selected($customer->customers_shipping_address_id == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Emergency Address</label>
        <select name="customers_emergency_address_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($customersEmergencyAddresses as $id => $name)
                <option value="{{ $id }}" @selected($customer->customers_emergency_address_id == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
