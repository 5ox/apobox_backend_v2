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
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'depth' => ['nullable', 'numeric', 'min:0'],
            'weight_lb' => ['nullable', 'integer', 'min:0'],
            'weight_oz' => ['nullable', 'numeric', 'min:0'],
            'customs_description' => ['nullable', 'string', 'max:255'],
            'mail_class' => ['nullable', 'string', 'max:32'],
            'insurance_coverage' => ['nullable', 'numeric', 'min:0'],
            'orders_status' => ['required', 'integer'],
            'custom_package_request_id' => ['nullable', 'integer'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
