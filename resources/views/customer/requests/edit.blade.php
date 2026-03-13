@extends('layouts.default')
@section('title', 'Edit Request - APO Box')
@section('content')
<x-page-header title="Edit Custom Package Request" />

<div class="row">
    <div class="col-lg-8">
        @if($packageRequest->orders_id && $packageRequest->orders_id !== '0')
            <div class="alert alert-warning d-flex gap-2 align-items-start">
                <i data-lucide="link" class="icon text-warning flex-shrink-0 mt-1"></i>
                <div>
                    This request is linked to <strong>Order #{{ $packageRequest->orders_id }}</strong>.
                    You can still update the tracking number and instructions.
                </div>
            </div>
        @endif

        <x-form-section title="Edit Request">
            <form method="POST" action="{{ url('/requests/edit/' . $packageRequest->custom_orders_id) }}">
                @csrf

                @if(in_array('tracking_id', $allowedFields))
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Inbound Tracking Number</label>
                        <input type="text" name="tracking_id" class="form-control" value="{{ old('tracking_id', $packageRequest->tracking_id) }}" maxlength="30" placeholder="e.g. 9400111899223100012345">
                        <div class="form-text">Enter the tracking number of the package being shipped to us.</div>
                    </div>
                @endif

                @if(in_array('package_repack', $allowedFields))
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Services Requested</label>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="package_repack" value="yes" id="repackCheck" @checked(old('package_repack', $packageRequest->package_repack) === 'yes')>
                            <label class="form-check-label" for="repackCheck">Repack my package</label>
                            <div class="form-text ms-4">We'll repack your items into a smaller or more secure box.</div>
                        </div>

                        @if(in_array('insurance_coverage', $allowedFields))
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="insuranceCheck" @checked(old('insurance_coverage', $packageRequest->insurance_coverage))>
                                <label class="form-check-label" for="insuranceCheck">Additional insurance coverage</label>
                                <div class="form-text ms-4">Add extra insurance for high-value items.</div>
                            </div>

                            @php $hasCoverage = old('insurance_coverage', $packageRequest->insurance_coverage); @endphp
                            <div id="insuranceFields" class="ms-4 mt-2" @unless($hasCoverage) style="display:none" @endunless>
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text">$</span>
                                    <input type="text" name="insurance_coverage" class="form-control" value="{{ old('insurance_coverage', $packageRequest->insurance_coverage) }}" maxlength="10" placeholder="0.00">
                                </div>
                                <div class="form-text">Enter the total value you'd like covered (USD).</div>
                            </div>
                        @endif
                    </div>
                @endif

                @if(in_array('instructions', $allowedFields))
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Special Instructions</label>
                        <textarea name="instructions" class="form-control" rows="4" placeholder="e.g. Combine with other packages, remove original packaging, add fragile labels, etc.">{{ old('instructions', $packageRequest->instructions) }}</textarea>
                        <div class="form-text">Describe any special handling you need for your package.</div>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ url('/requests') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </x-form-section>
    </div>

    <div class="col-lg-4">
        <x-form-section title="Request Info">
            <ul class="list-unstyled mb-0">
                <li class="mb-2"><strong>Status:</strong> {{ $packageRequest->status_label }}</li>
                <li class="mb-2"><strong>Created:</strong> {{ $packageRequest->order_add_date?->format('m/d/Y') }}</li>
                @if($packageRequest->orders_id && $packageRequest->orders_id !== '0')
                    <li class="mb-2"><strong>Order:</strong> #{{ $packageRequest->orders_id }}</li>
                @endif
                @if($packageRequest->tracking_id && $packageRequest->tracking_id !== '0')
                    <li class="mb-2"><strong>Tracking:</strong> {{ $packageRequest->tracking_id }}</li>
                @endif
                @if($packageRequest->package_repack === 'yes')
                    <li class="mb-2"><strong>Repack:</strong> Yes</li>
                @endif
                @if($packageRequest->insurance_coverage)
                    <li class="mb-2"><strong>Insurance:</strong> ${{ $packageRequest->insurance_coverage }}</li>
                @endif
            </ul>
        </x-form-section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const insuranceCheck = document.getElementById('insuranceCheck');
    const insuranceFields = document.getElementById('insuranceFields');
    if (!insuranceCheck || !insuranceFields) return;

    insuranceCheck.addEventListener('change', function () {
        insuranceFields.style.display = this.checked ? '' : 'none';
        if (!this.checked) {
            insuranceFields.querySelector('input[name="insurance_coverage"]').value = '';
        }
    });
});
</script>
@endpush
