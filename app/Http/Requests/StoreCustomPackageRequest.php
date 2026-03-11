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
            'description' => ['required', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'in:pending,approved,completed,denied'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'A description of the request is required.',
        ];
    }
}
