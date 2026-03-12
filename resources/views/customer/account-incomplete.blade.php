@extends('layouts.default')
@section('title', 'Complete Your Account - APO Box')
@section('content')
<h2>Complete Your Account</h2>
<p>Please complete your account information to start using APO Box.</p>
<form method="POST" action="{{ url('/customers/edit/my_info') }}">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="customers_firstname" class="form-control" value="{{ old('customers_firstname', $customer->customers_firstname) }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="customers_lastname" class="form-control" value="{{ old('customers_lastname', $customer->customers_lastname) }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Telephone</label>
        <input type="tel" name="customers_telephone" class="form-control" value="{{ old('customers_telephone', $customer->customers_telephone) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">State</label>
        <select name="entry_zone_id" class="form-select">
            <option value="">-- Select State --</option>
            @foreach($zones as $id => $name)
                <option value="{{ $id }}" @selected(old('entry_zone_id') == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Continue</button>
</form>
@endsection
