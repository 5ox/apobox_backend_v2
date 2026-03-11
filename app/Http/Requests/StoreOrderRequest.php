<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'customers_id' => ['required', 'integer', 'exists:customers,customers_id'],
            'delivery_name' => ['required', 'string', 'max:64'],
            'delivery_street_address' => ['required', 'string', 'max:255'],
            'delivery_city' => ['required', 'string', 'max:64'],
            'delivery_state' => ['nullable', 'string', 'max:32'],
            'delivery_postcode' => ['required', 'string', 'max:10'],
            'delivery_country' => ['required', 'string', 'max:64'],
            'mail_class' => ['required', 'string'],
            'package_type' => ['required', 'string'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'depth' => ['nullable', 'numeric', 'min:0'],
            'weight_oz' => ['nullable', 'numeric', 'min:0'],
            'insurance_coverage' => ['nullable', 'numeric', 'min:0'],
            'comments' => ['nullable', 'string', 'max:1000'],
            'usps_track_num_in' => ['nullable', 'string', 'max:64'],
        ];
    }
}
