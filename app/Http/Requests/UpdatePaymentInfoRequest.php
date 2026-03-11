<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'cc_firstname' => ['required', 'string', 'max:64'],
            'cc_lastname' => ['required', 'string', 'max:64'],
            'cc_number' => ['required', 'string', 'max:20'],
            'cc_expires_month' => ['required', 'string', 'size:2', 'regex:/^(0[1-9]|1[0-2])$/'],
            'cc_expires_year' => ['required', 'string', 'size:2', 'regex:/^\d{2}$/'],
            'cc_cvv' => ['required', 'string', 'min:3', 'max:4', 'regex:/^\d+$/'],
            'billing_type' => ['nullable', 'string', 'in:cc,invoice'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'cc_firstname.required' => 'Cardholder first name is required.',
            'cc_lastname.required' => 'Cardholder last name is required.',
            'cc_number.required' => 'Credit card number is required.',
            'cc_expires_month.required' => 'Expiration month is required.',
            'cc_expires_year.required' => 'Expiration year is required.',
            'cc_cvv.required' => 'CVV is required.',
        ];
    }
}
