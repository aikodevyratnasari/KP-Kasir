<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role->slug, ['admin', 'manager']);
    }

    public function rules(): array
    {
        $category = $this->route('category');
        // Cast ke int agar tidak terjadi type mismatch di PostgreSQL
        $storeId  = (int) ($this->user()->store_id ?? $category->store_id ?? 0);

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')
                    ->ignore($category->id)
                    ->where(fn($q) => $q->where('store_id', $storeId))
                    ->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
}