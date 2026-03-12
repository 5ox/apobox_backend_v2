@extends('layouts.default')
@section('title', 'Pay Order #' . $order->orders_id . ' - APO Box')
@section('content')
<x-page-header :title="'Pay Order #' . $order->orders_id" />

<x-table-card title="Order Charges">
    <table class="table table-modern">
        <tbody>
            @foreach($orderCharges as $charge)
                <tr @if($charge->class === 'ot_total') class="fw-bold" @endif>
                    <td>{{ $charge->title }}</td>
                    <td class="text-end">${{ number_format($charge->value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-table-card>

<x-form-section title="Payment">
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
        <button type="submit" class="btn btn-primary"><i data-lucide="credit-card" class="icon--sm"></i> Pay Now</button>
        <a href="{{ url('/orders/' . $order->orders_id) }}" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
