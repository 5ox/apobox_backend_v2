<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
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
        $adminId = $this->route('id');

        return [
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('admins', 'email')->ignore($adminId),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:sysadmin,manager,employee,api'],
        ];
    }
}
