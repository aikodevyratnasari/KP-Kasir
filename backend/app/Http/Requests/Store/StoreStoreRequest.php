<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:100'],
            'address'        => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:100', 'unique:stores,email'],
            'tax_number'     => ['nullable', 'string', 'max:50'],
            'tax_rate'       => ['required', 'numeric', 'min:0', 'max:100'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'has_kitchen'    => ['nullable', 'boolean'],
            'logo'           => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama toko wajib diisi.',
            'name.max'          => 'Nama toko maksimal 100 karakter.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah digunakan toko lain.',
            'tax_rate.required' => 'Tarif pajak wajib diisi.',
            'tax_rate.numeric'  => 'Tarif pajak harus berupa angka.',
            'tax_rate.min'      => 'Tarif pajak tidak boleh kurang dari 0.',
            'tax_rate.max'      => 'Tarif pajak tidak boleh lebih dari 100.',
            'logo.image'        => 'File logo harus berupa gambar.',
            'logo.mimes'        => 'Format logo harus PNG, JPG, JPEG, atau WEBP.',
            'logo.max'          => 'Ukuran logo maksimal 1 MB.',
        ];
    }
}