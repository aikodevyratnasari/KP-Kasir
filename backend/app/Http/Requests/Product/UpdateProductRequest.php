<?php
namespace App\Http\Requests\Product;

class UpdateProductRequest extends StoreProductRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'];
        return $rules;
    }
}
