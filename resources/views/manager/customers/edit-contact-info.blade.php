@extends('layouts.manager')
@section('title', 'Edit Contact Info - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Edit Contact Info - {{ $customer->full_name }}" />

<x-form-section title="Edit Contact Information">
    <form method="POST" action="/{{ $prefix }}/customers/{{ $customer->customers_id }}/edit/contact-info">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">First Name</label><input type="text" name="customers_firstname" class="form-control" value="{{ old('customers_firstname', $customer->customers_firstname) }}"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Last Name</label><input type="text" name="customers_lastname" class="form-control" value="{{ old('customers_lastname', $customer->customers_lastname) }}"></div>
        </div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="customers_email_address" class="form-control" value="{{ old('customers_email_address', $customer->customers_email_address) }}"></div>
        <div class="mb-3"><label class="form-label">Backup Email</label><input type="email" name="backup_email_address" class="form-control" value="{{ old('backup_email_address', $customer->backup_email_address) }}"></div>
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Telephone</label><input type="tel" name="customers_telephone" class="form-control" value="{{ old('customers_telephone', $customer->customers_telephone) }}"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Cell Phone</label><input type="tel" name="customers_fax" class="form-control" value="{{ old('customers_fax', $customer->customers_fax) }}"></div>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
