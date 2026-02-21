<?php
namespace App\Http\Requests\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        return [
            'category_id'     => ['required', 'exists:categories,id'],
            'name'            => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'price'           => ['required', 'numeric', 'min:0'],
            'stock'           => ['required', 'integer', 'min:0'],
            'low_stock_alert' => ['nullable', 'integer', 'min:0'],
            'is_available'    => ['boolean'],
            'track_stock'     => ['boolean'],
        ];
    }
    public function messages(): array { return ['price.min' => 'Harga tidak boleh negatif.', 'stock.min' => 'Stok tidak boleh negatif.']; }
}
