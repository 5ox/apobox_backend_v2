@extends('layouts.manager')
@section('title', 'Edit Insurance Tier - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Edit Insurance Tier" />

<x-form-section title="Edit Insurance Tier">
    <form method="POST" action="/{{ $prefix }}/settings/insurance-tiers/edit/{{ $tier->insurance_id }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Coverage From ($)</label>
                <input type="number" name="amount_from" class="form-control @error('amount_from') is-invalid @enderror"
                       value="{{ old('amount_from', $tier->amount_from) }}" step="0.01" min="0" required>
                @error('amount_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Coverage To ($)</label>
                <input type="number" name="amount_to" class="form-control @error('amount_to') is-invalid @enderror"
                       value="{{ old('amount_to', $tier->amount_to) }}" step="0.01" min="0" required>
                @error('amount_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Insurance Fee ($)</label>
                <input type="number" name="insurance_fee" class="form-control @error('insurance_fee') is-invalid @enderror"
                       value="{{ old('insurance_fee', $tier->insurance_fee) }}" step="0.01" min="0" required>
                @error('insurance_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="/{{ $prefix }}/settings/insurance-tiers" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-form-section>
@endsection
