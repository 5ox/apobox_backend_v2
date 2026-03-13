@extends('layouts.manager')
@section('title', 'Edit Authorized Name - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Edit Authorized Name" />

<x-form-section title="Name Details">
    <form method="POST" action="/{{ $prefix }}/authorized_names/{{ $authorizedName->authorized_names_id }}/edit">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">First Name</label><input type="text" name="authorized_firstname" class="form-control" value="{{ old('authorized_firstname', $authorizedName->authorized_firstname) }}" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Last Name</label><input type="text" name="authorized_lastname" class="form-control" value="{{ old('authorized_lastname', $authorizedName->authorized_lastname) }}" required></div>
        </div>
        <button type="submit" class="btn btn-primary"><i data-lucide="save" class="icon-sm me-1"></i>Save</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
