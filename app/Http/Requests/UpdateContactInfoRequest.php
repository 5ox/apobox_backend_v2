<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactInfoRequest extends FormRequest
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
        $customerId = $this->route('id');

        return [
            'customers_firstname' => ['required', 'string', 'max:64'],
            'customers_lastname' => ['required', 'string', 'max:64'],
            'customers_email_address' => [
                'required', 'email', 'max:96',
                Rule::unique('customers', 'customers_email_address')->ignore($customerId, 'customers_id'),
            ],
            'customers_telephone' => ['required', 'string', 'max:32'],
            'customers_fax' => ['nullable', 'string', 'max:32'],
            'backup_email_address' => ['nullable', 'email', 'max:96'],
        ];
    }
}
