<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
            'entry_firstname' => ['required', 'string', 'max:64'],
            'entry_lastname' => ['required', 'string', 'max:64'],
            'entry_company' => ['nullable', 'string', 'max:64'],
            'entry_street_address' => ['required', 'string', 'max:255'],
            'entry_suburb' => ['nullable', 'string', 'max:64'],
            'entry_city' => ['required', 'string', 'max:64'],
            'entry_state' => ['nullable', 'string', 'max:32'],
            'entry_postcode' => ['required', 'string', 'max:10'],
            'entry_country_id' => ['required', 'integer'],
            'entry_zone_id' => ['nullable', 'integer'],
            'entry_gender' => ['nullable', 'string', 'in:m,f'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'entry_firstname.required' => 'First name is required.',
            'entry_lastname.required' => 'Last name is required.',
            'entry_street_address.required' => 'Street address is required.',
            'entry_city.required' => 'City is required.',
            'entry_postcode.required' => 'Postal code is required.',
            'entry_country_id.required' => 'Country is required.',
        ];
    }
}
