@extends('layouts.default')
@section('title', 'Edit Address - APO Box')
@section('content')
<h2>Edit Address: {{ $addressName }}</h2>
<form method="POST" action="{{ url('/address/' . $addressId . '/edit') }}">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="entry_firstname" class="form-control" value="{{ old('entry_firstname', $address->entry_firstname) }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="entry_lastname" class="form-control" value="{{ old('entry_lastname', $address->entry_lastname) }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Company</label>
        <input type="text" name="entry_company" class="form-control" value="{{ old('entry_company', $address->entry_company) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Street Address</label>
        <input type="text" name="entry_street_address" class="form-control" value="{{ old('entry_street_address', $address->entry_street_address) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Address Line 2</label>
        <input type="text" name="entry_suburb" class="form-control" value="{{ old('entry_suburb', $address->entry_suburb) }}">
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">City</label>
            <input type="text" name="entry_city" class="form-control" value="{{ old('entry_city', $address->entry_city) }}" required>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">State</label>
            <select name="entry_zone_id" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach($zones as $id => $name)
                    <option value="{{ $id }}" @selected(old('entry_zone_id', $address->entry_zone_id) == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Zip Code</label>
            <input type="text" name="entry_postcode" class="form-control" value="{{ old('entry_postcode', $address->entry_postcode) }}" required>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="{{ url('/account') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
