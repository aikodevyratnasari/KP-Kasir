<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role->slug, ['admin', 'manager']);
    }

    public function rules(): array
    {
        // stock hanya required jika track_stock dicentang
        $trackStock = $this->boolean('track_stock');

        return [
            'category_id'     => ['required', 'exists:categories,id'],
            'name'            => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'price'           => ['required', 'numeric', 'min:0'],
            'stock'           => $trackStock ? ['required', 'integer', 'min:0'] : ['nullable', 'integer', 'min:0'],
            'low_stock_alert' => ['nullable', 'integer', 'min:0'],
            'is_available'    => ['nullable', 'boolean'],
            'track_stock'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.required'  => 'Harga wajib diisi.',
            'price.min'       => 'Harga tidak boleh negatif.',
            'stock.required'  => 'Stok wajib diisi jika lacak stok diaktifkan.',
            'stock.min'       => 'Stok tidak boleh negatif.',
        ];
    }

    /**
     * Pastikan nilai boolean dikirim dengan benar dari checkbox HTML.
     * Checkbox HTML tidak mengirim value saat tidak dicentang,
     * jadi kita perlu normalize di sini.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_available' => $this->has('is_available') ? 1 : 0,
            'track_stock'  => $this->has('track_stock')  ? 1 : 0,
            // Jika track_stock off, stock default 0
            'stock'        => $this->has('track_stock') ? $this->input('stock') : 0,
        ]);
    }
}