@extends('layouts.default')
@section('title', 'Pay Order #' . $order->orders_id . ' - APO Box')
@section('content')
<x-page-header :title="'Pay Order #' . $order->orders_id" />

<form method="POST" action="{{ url('/orders/' . $order->orders_id . '/pay') }}" id="payForm">
    @csrf
    <div class="row">
        {{-- Left column — charges & billing address --}}
        <div class="col-lg-7">
            <x-table-card title="Order Charges">
                <table class="table table-modern mb-0">
                    <tbody>
                        @foreach($orderCharges as $charge)
                            <tr @if($charge->class === 'ot_total') class="fw-bold" @endif>
                                <td>{{ $charge->title }}</td>
                                <td class="text-end">${{ number_format($charge->value, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-card>

            <x-form-section title="Billing Address">
                <div class="mb-3">
                    <label class="form-label">Select a billing address</label>
                    <select name="customers_default_address_id" class="form-select">
                        @foreach($addresses as $id => $name)
                            <option value="{{ $id }}" @selected($selected == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </x-form-section>
        </div>

        {{-- Right column — payment method --}}
        <div class="col-lg-5">
            <x-form-section title="Payment Method">
                @if($customer->cc_number)
                    <div id="cardOnFile" class="mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i data-lucide="credit-card" class="icon text-primary"></i>
                            <span class="fw-semibold">Card on File</span>
                        </div>
                        <div class="ps-4 text-secondary">
                            <div>{{ $customer->cc_firstname }} {{ $customer->cc_lastname }}</div>
                            <div>{{ $customer->cc_number }}</div>
                            <div>Expires {{ $customer->cc_expires_month }}/{{ $customer->cc_expires_year }}</div>
                        </div>
                    </div>

                    {{-- Hidden fields pre-filled with card on file --}}
                    <input type="hidden" name="cc_firstname" id="hid_cc_firstname" value="{{ $customer->cc_firstname }}">
                    <input type="hidden" name="cc_lastname" id="hid_cc_lastname" value="{{ $customer->cc_lastname }}">
                    <input type="hidden" name="cc_number" id="hid_cc_number" value="{{ $customer->cc_number }}">
                    <input type="hidden" name="cc_expires_month" id="hid_cc_expires_month" value="{{ $customer->cc_expires_month }}">
                    <input type="hidden" name="cc_expires_year" id="hid_cc_expires_year" value="{{ $customer->cc_expires_year }}">
                    <input type="hidden" name="cc_cvv" id="hid_cc_cvv" value="000">

                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="toggleCardFields">
                        <i data-lucide="refresh-cw" class="icon--sm"></i> Update Credit Card
                    </button>
                @endif

                <div id="cardFields" @if($customer->cc_number) style="display:none" @endif>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name on Card</label>
                            <input type="text" name="cc_firstname" class="form-control cc-field" value="{{ old('cc_firstname') }}" autocomplete="cc-given-name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name on Card</label>
                            <input type="text" name="cc_lastname" class="form-control cc-field" value="{{ old('cc_lastname') }}" autocomplete="cc-family-name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Card Number</label>
                        <input type="text" name="cc_number" class="form-control cc-field" value="{{ old('cc_number') }}" autocomplete="cc-number" inputmode="numeric">
                    </div>
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label">Exp Month</label>
                            <input type="text" name="cc_expires_month" class="form-control cc-field" maxlength="2" placeholder="MM" value="{{ old('cc_expires_month') }}" autocomplete="cc-exp-month" inputmode="numeric">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Exp Year</label>
                            <input type="text" name="cc_expires_year" class="form-control cc-field" maxlength="2" placeholder="YY" value="{{ old('cc_expires_year') }}" autocomplete="cc-exp-year" inputmode="numeric">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" name="cc_cvv" class="form-control cc-field" maxlength="4" value="{{ old('cc_cvv') }}" autocomplete="cc-csc" inputmode="numeric">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="save" value="1" id="saveCard" checked>
                        <label class="form-check-label" for="saveCard">Save card for future auto-billing</label>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="lock" class="icon--sm"></i> Pay ${{ number_format($order->total?->value ?? 0, 2) }}
                    </button>
                    <a href="{{ url('/orders/' . $order->orders_id) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </x-form-section>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleCardFields');
    if (!toggle) return;

    const cardOnFile = document.getElementById('cardOnFile');
    const cardFields = document.getElementById('cardFields');
    const hiddenInputs = document.querySelectorAll('input[id^="hid_"]');
    const visibleInputs = cardFields.querySelectorAll('.cc-field');
    let showingFields = false;

    toggle.addEventListener('click', function () {
        showingFields = !showingFields;
        cardFields.style.display = showingFields ? '' : 'none';

        if (showingFields) {
            // Disable hidden inputs so visible fields are submitted
            hiddenInputs.forEach(el => el.disabled = true);
            visibleInputs.forEach(el => el.disabled = false);
            toggle.innerHTML = '<i data-lucide="undo-2" class="icon--sm"></i> Use Card on File';
            lucide.createIcons();
        } else {
            // Re-enable hidden inputs, disable visible fields
            hiddenInputs.forEach(el => el.disabled = false);
            visibleInputs.forEach(el => el.disabled = true);
            toggle.innerHTML = '<i data-lucide="refresh-cw" class="icon--sm"></i> Update Credit Card';
            lucide.createIcons();
        }
    });

    // On load: if card on file, disable visible cc fields so hidden ones are submitted
    visibleInputs.forEach(el => el.disabled = true);
});
</script>
@endpush
