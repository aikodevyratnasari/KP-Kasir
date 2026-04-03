<?php
namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role->slug, ['admin', 'manager']);
    }

    public function rules(): array
    {
        // Cast ke int agar tidak terjadi type mismatch di PostgreSQL
        $storeId = (int) ($this->user()->store_id ?? 0);

        return [
            'name'        => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')
                    ->where(fn ($q) => $q
                        ->where('store_id', $storeId)
                        ->whereNull('deleted_at')
                    ),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return ['name.unique' => 'Nama kategori sudah digunakan di toko ini.'];
    }
}