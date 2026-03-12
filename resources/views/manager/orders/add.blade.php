@extends('layouts.manager')
@section('title', 'New Order - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>New Order for {{ $customer->full_name }}</h2>
<form method="POST" action="/{{ $prefix }}/orders/add/{{ $customer->customers_id }}">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3"><label class="form-label">Inbound Tracking</label><input type="text" name="inbound_tracking" class="form-control" value="{{ old('inbound_tracking') }}"></div>
            <div class="mb-3"><label class="form-label">Dimensions</label><input type="text" name="dimensions" class="form-control" value="{{ old('dimensions') }}" placeholder="LxWxH"></div>
            <div class="mb-3"><label class="form-label">Weight (lb)</label><input type="text" name="weight" class="form-control" value="{{ old('weight') }}"></div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="orders_status" class="form-select">
                    @foreach($orderStatuses as $id => $name)
                        <option value="{{ $id }}" @selected(old('orders_status') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Shipping Address</label>
                <select name="address_id" class="form-select">
                    @foreach($customersAddresses as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @if($requests->isNotEmpty())
        <div class="mb-3">
            <label class="form-label">Link Custom Request</label>
            <select name="custom_package_request_id" class="form-select">
                <option value="">-- None --</option>
                @foreach($requests as $req)
                    <option value="{{ $req->custom_orders_id }}">{{ $req->instructions }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <button type="submit" class="btn btn-primary">Create Order</button>
    <a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
