<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->user()?->customers_id;

        return [
            'customers_firstname' => ['sometimes', 'required', 'string', 'max:64'],
            'customers_lastname' => ['sometimes', 'required', 'string', 'max:64'],
            'customers_email_address' => [
                'sometimes', 'required', 'email', 'max:96',
                Rule::unique('customers', 'customers_email_address')->ignore($customerId, 'customers_id'),
            ],
            'customers_telephone' => ['sometimes', 'required', 'string', 'max:32'],
            'customers_fax' => ['nullable', 'string', 'max:32'],
            'customers_gender' => ['nullable', 'string', 'in:m,f'],
            'customers_dob' => ['nullable', 'date'],
            'customers_newsletter' => ['nullable', 'boolean'],
            'backup_email_address' => ['nullable', 'email', 'max:96'],
            'default_postal_type' => ['nullable', 'string', 'max:32'],
        ];
    }
}
