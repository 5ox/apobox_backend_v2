@extends('layouts.default')
@section('title', 'Edit - APO Box')
@section('content')
<h2>Edit {{ ucwords(str_replace('_', ' ', $partial)) }}</h2>
<form method="POST" action="{{ url('/customers/edit/' . $partial) }}">
    @csrf
    @if($partial === 'my_info')
        @foreach($fields as $field)
            <div class="mb-3">
                <label class="form-label">{{ ucwords(str_replace(['customers_', '_'], ['', ' '], $field)) }}</label>
                <input type="{{ $field === 'customers_email_address' || $field === 'backup_email_address' ? 'email' : 'text' }}" name="{{ $field }}" class="form-control" value="{{ old($field, $customer->$field) }}">
            </div>
        @endforeach
    @elseif($partial === 'payment_info')
        <div class="mb-3">
            <label class="form-label">First Name on Card</label>
            <input type="text" name="cc_firstname" class="form-control" value="{{ old('cc_firstname', $customer->cc_firstname) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Last Name on Card</label>
            <input type="text" name="cc_lastname" class="form-control" value="{{ old('cc_lastname', $customer->cc_lastname) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Card Number</label>
            <input type="text" name="cc_number" class="form-control" value="{{ old('cc_number') }}">
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Expiry Month</label>
                <select name="cc_expires_month" class="form-select">
                    @foreach($months as $num => $label)
                        <option value="{{ $num }}" @selected(old('cc_expires_month', $customer->cc_expires_month) == $num)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Expiry Year</label>
                <select name="cc_expires_year" class="form-select">
                    @foreach($years as $num => $label)
                        <option value="{{ $num }}" @selected(old('cc_expires_year', $customer->cc_expires_year) == $num)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">CVV</label>
                <input type="text" name="cc_cvv" class="form-control" maxlength="4">
            </div>
        </div>
    @elseif($partial === 'addresses')
        @foreach(['customers_default_address_id' => 'Billing Address', 'customers_shipping_address_id' => 'Shipping Address', 'customers_emergency_address_id' => 'Backup Shipping Address'] as $field => $label)
            <div class="mb-3">
                <label class="form-label">{{ $label }}</label>
                <select name="{{ $field }}" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach($addresses as $id => $name)
                        <option value="{{ $id }}" @selected(old($field, $customer->$field) == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    @elseif($partial === 'shipping')
        <div class="mb-3">
            <label class="form-label">Insurance Amount</label>
            <input type="number" name="insurance_amount" class="form-control" step="0.01" value="{{ old('insurance_amount', $customer->insurance_amount) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Default Postal Type</label>
            <select name="default_postal_type" class="form-select">
                @foreach($postalClasses as $key => $label)
                    <option value="{{ $key }}" @selected(old('default_postal_type', $customer->default_postal_type) == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="{{ url('/account') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
