<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorizedNameRequest extends FormRequest
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
            'authorized_firstname' => ['required', 'string', 'max:64'],
            'authorized_lastname' => ['required', 'string', 'max:64'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'authorized_firstname.required' => 'First name is required.',
            'authorized_lastname.required' => 'Last name is required.',
        ];
    }
}
