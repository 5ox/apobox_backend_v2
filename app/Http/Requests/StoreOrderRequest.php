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
            'address_id' => ['required', 'integer', 'exists:address_book,address_book_id'],
            'inbound_tracking' => ['nullable', 'string', 'max:128'],
            'dimensions' => ['nullable', 'string', 'max:32'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'orders_status' => ['required', 'integer'],
            'custom_package_request_id' => ['nullable', 'integer'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
