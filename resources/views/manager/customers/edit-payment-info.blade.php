@extends('layouts.manager')
@section('title', 'Edit Payment Info - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Edit Payment Info - {{ $customer->full_name }}" />

<x-form-section title="Edit Payment Information">
    <form method="POST" action="/{{ $prefix }}/customers/{{ $customer->customers_id }}/edit/payment-info">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">First Name on Card</label><input type="text" name="cc_firstname" class="form-control" value="{{ old('cc_firstname', $customer->cc_firstname) }}"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Last Name on Card</label><input type="text" name="cc_lastname" class="form-control" value="{{ old('cc_lastname', $customer->cc_lastname) }}"></div>
        </div>
        <div class="mb-3"><label class="form-label">Card Number</label><input type="text" name="cc_number" class="form-control"></div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label">Exp Month</label><input type="text" name="cc_expires_month" class="form-control" maxlength="2"></div>
            <div class="col-md-4 mb-3"><label class="form-label">Exp Year</label><input type="text" name="cc_expires_year" class="form-control" maxlength="2"></div>
            <div class="col-md-4 mb-3"><label class="form-label">CVV</label><input type="text" name="cc_cvv" class="form-control" maxlength="4"></div>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
