@extends('layouts.default')
@section('title', 'Almost Finished - APO Box')
@section('content')
<h2>Almost Finished!</h2>
<p>Please complete your shipping and payment information.</p>
<form method="POST" action="{{ url('/customers/edit/shipping') }}">
    @csrf
    <h4>Shipping Address</h4>
    <div class="mb-3">
        <label class="form-label">Select Address</label>
        <select name="customers_shipping_address_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($addresses as $id => $name)
                <option value="{{ $id }}" @selected(old('customers_shipping_address_id', $customer->customers_shipping_address_id) == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">State</label>
        <select name="entry_zone_id" class="form-select">
            @foreach($zones as $id => $name)
                <option value="{{ $id }}" @selected(old('entry_zone_id') == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    @if(!empty($sources))
        <div class="mb-3">
            <label class="form-label">How did you hear about us?</label>
            <select name="source" class="form-select">
                <option value="">-- Select --</option>
                @foreach($sources as $source)
                    <option value="{{ $source }}">{{ $source }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <button type="submit" class="btn btn-primary">Save & Continue</button>
</form>
@endsection
