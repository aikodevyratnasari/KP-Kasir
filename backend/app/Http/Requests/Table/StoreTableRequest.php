<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role->slug, ['admin', 'manager']);
    }

    public function rules(): array
    {
        $storeId = (int) ($this->user()->store_id ?? 0);

        return [
            'number'   => [
                'required', 'string', 'max:20',
                Rule::unique('tables', 'number')
                    ->where(fn ($q) => $q->where('store_id', $storeId)),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'section'  => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return ['number.unique' => 'Nomor meja sudah digunakan'];
    }
}