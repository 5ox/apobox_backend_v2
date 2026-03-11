<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChargeOrderRequest extends FormRequest
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
            'orders_id' => ['required', 'integer', 'exists:orders,orders_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', 'in:cc,invoice'],
            'cc_number' => ['nullable', 'string', 'max:20'],
            'cc_expires' => ['nullable', 'string', 'max:5'],
            'cc_cvv' => ['nullable', 'string', 'min:3', 'max:4'],
        ];
    }
}
