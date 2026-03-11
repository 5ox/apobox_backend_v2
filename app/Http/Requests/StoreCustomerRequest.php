<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            'customers_firstname' => ['required', 'string', 'max:64'],
            'customers_lastname' => ['required', 'string', 'max:64'],
            'customers_email_address' => ['required', 'email', 'max:96', 'unique:customers,customers_email_address'],
            'customers_password' => ['required', 'string', 'min:8', 'confirmed'],
            'customers_telephone' => ['required', 'string', 'max:32'],
            'customers_gender' => ['nullable', 'string', 'in:m,f'],
            'customers_dob' => ['nullable', 'date'],
            'customers_newsletter' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'customers_firstname.required' => 'First name is required.',
            'customers_lastname.required' => 'Last name is required.',
            'customers_email_address.required' => 'Email address is required.',
            'customers_email_address.unique' => 'This email address is already registered.',
            'customers_password.min' => 'Password must be at least 8 characters.',
        ];
    }
}
