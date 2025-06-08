<?php

namespace App\Http\Requests\pharmacy\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules()
    {
        return [
            'medicine_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ];
    }
    public function messages()
    {
        return [
            'medicine_id.required' => 'Medicine id is required.',
            'medicine_id.exists' => 'Medicine not found.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}
