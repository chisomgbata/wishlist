<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToWishlistRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
        ];
    }

    public function authorize(): bool
    {
        // in a production app you would check if the user has the right to add to the wishlist
        return true;
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'The product ID is required.',
            'product_id.exists' => 'The product you are trying to add to the wishlist does not exist.',
        ];
    }
}
