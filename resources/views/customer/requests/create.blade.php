@extends('layouts.default')
@section('title', 'New Request - APO Box')
@section('content')
<x-page-header title="New Custom Package Request" />

<div class="row">
    <div class="col-lg-8">
        <div class="alert alert-info d-flex gap-2 align-items-start">
            <i data-lucide="info" class="icon text-info flex-shrink-0 mt-1"></i>
            <div>
                <strong>What is a Custom Package Request?</strong><br>
                Use this form to request special handling for a package being shipped to your APO Box.
                Available services include repacking, custom packing, additional insurance, consolidation, and more.
                Add your inbound tracking number so we can match your package when it arrives.
            </div>
        </div>

        <x-form-section title="Request Details">
            <form method="POST" action="{{ url('/requests/add') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-semibold">Inbound Tracking Number</label>
                    <input type="text" name="tracking_id" class="form-control" value="{{ old('tracking_id') }}" maxlength="30" placeholder="e.g. 9400111899223100012345">
                    <div class="form-text">Enter the tracking number of the package being shipped to us. This helps us match your package to this request when it arrives.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Services Requested</label>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="package_repack" value="yes" id="repackCheck" @checked(old('package_repack') === 'yes')>
                        <label class="form-check-label" for="repackCheck">Repack my package</label>
                        <div class="form-text ms-4">We'll repack your items into a smaller or more secure box to reduce shipping costs or improve protection.</div>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="insuranceCheck" @checked(old('insurance_coverage'))>
                        <label class="form-check-label" for="insuranceCheck">Additional insurance coverage</label>
                        <div class="form-text ms-4">Add extra insurance beyond the standard carrier coverage for high-value items.</div>
                    </div>

                    <div id="insuranceFields" class="ms-4 mt-2" @unless(old('insurance_coverage')) style="display:none" @endunless>
                        <div class="input-group" style="max-width: 200px;">
                            <span class="input-group-text">$</span>
                            <input type="text" name="insurance_coverage" class="form-control" value="{{ old('insurance_coverage') }}" maxlength="10" placeholder="0.00">
                        </div>
                        <div class="form-text">Enter the total value you'd like covered (USD).</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Special Instructions</label>
                    <textarea name="instructions" class="form-control" rows="4" placeholder="e.g. Combine with other packages, remove original packaging, add fragile labels, etc.">{{ old('instructions') }}</textarea>
                    <div class="form-text">Describe any special handling you need for your package. Be as specific as possible.</div>
                </div>

                <button type="submit" class="btn btn-primary"><i data-lucide="send" class="icon--sm"></i> Submit Request</button>
                <a href="{{ url('/requests') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </x-form-section>
    </div>

    <div class="col-lg-4">
        <x-form-section title="Available Services">
            <ul class="list-unstyled mb-0">
                <li class="mb-3">
                    <i data-lucide="package" class="icon--sm text-primary"></i>
                    <strong>Repacking</strong><br>
                    <small class="text-secondary">Repack items into a smaller or sturdier box</small>
                </li>
                <li class="mb-3">
                    <i data-lucide="shield" class="icon--sm text-primary"></i>
                    <strong>Additional Insurance</strong><br>
                    <small class="text-secondary">Extra coverage for valuable items</small>
                </li>
                <li class="mb-3">
                    <i data-lucide="combine" class="icon--sm text-primary"></i>
                    <strong>Consolidation</strong><br>
                    <small class="text-secondary">Combine multiple packages into one shipment</small>
                </li>
                <li class="mb-3">
                    <i data-lucide="shield-alert" class="icon--sm text-primary"></i>
                    <strong>Fragile Handling</strong><br>
                    <small class="text-secondary">Extra padding and fragile labels</small>
                </li>
                <li>
                    <i data-lucide="pen-line" class="icon--sm text-primary"></i>
                    <strong>Custom Requests</strong><br>
                    <small class="text-secondary">Anything else — just describe it in the instructions</small>
                </li>
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
