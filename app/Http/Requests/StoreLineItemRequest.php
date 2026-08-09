<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLineItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100', 'prohibits:discount_fixed'],
            'discount_fixed' => ['nullable', 'numeric', 'min:0', 'prohibits:discount_percent'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'Quantity must be at least 1.',
            'unit_price.min' => 'Unit price cannot be negative.',
            'discount_percent.prohibits' => 'A line may have a percent discount or a fixed discount, not both.',
            'discount_fixed.prohibits' => 'A line may have a percent discount or a fixed discount, not both.',
            'discount_percent.max' => 'Discount percent cannot exceed 100.',
            'tax_percent.max' => 'Tax percent cannot exceed 100.',
        ];
    }
}
