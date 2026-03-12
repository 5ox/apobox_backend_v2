@extends('layouts.manager')
@section('title', 'Add Affiliate Link - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Add Affiliate Link" />

<x-form-section title="Create Affiliate Link">
    <form method="POST" action="/{{ $prefix }}/affiliate-links/add">
        @csrf
        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
        <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" value="{{ old('code') }}" required></div>
        <div class="mb-3"><label class="form-label">URL</label><input type="url" name="url" class="form-control" value="{{ old('url') }}"></div>
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="/{{ $prefix }}/affiliate-links" class="btn btn-secondary">Cancel</a>
    </form>
</x-form-section>
@endsection
