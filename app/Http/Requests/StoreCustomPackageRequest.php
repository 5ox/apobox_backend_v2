<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomPackageRequest extends FormRequest
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
            'orders_id' => ['nullable', 'integer', 'exists:orders,orders_id'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'package_status' => ['nullable', 'integer', 'in:1,2,3,4'],
            'tracking_id' => ['nullable', 'string', 'max:30'],
            'package_repack' => ['nullable', 'string', 'in:yes,no'],
            'insurance_coverage' => ['nullable', 'string', 'max:10'],
        ];
    }
}
