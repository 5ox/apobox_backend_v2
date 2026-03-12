@extends('layouts.default')
@section('title', 'Pay Order #' . $order->orders_id . ' - APO Box')
@section('content')
<h2>Pay Order #{{ $order->orders_id }}</h2>
<h4>Order Charges</h4>
<table class="table table-sm">
    <tbody>
        @foreach($orderCharges as $charge)
            <tr @if($charge->class === 'ot_total') class="fw-bold" @endif>
                <td>{{ $charge->title }}</td>
                <td class="text-end">${{ number_format($charge->value, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<h4>Payment</h4>
<form method="POST" action="{{ url('/orders/' . $order->orders_id . '/pay') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Shipping Address</label>
        <select name="address_id" class="form-select">
            @foreach($addresses as $id => $name)
                <option value="{{ $id }}" @selected($selected == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Pay Now</button>
    <a href="{{ url('/orders/' . $order->orders_id) }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
