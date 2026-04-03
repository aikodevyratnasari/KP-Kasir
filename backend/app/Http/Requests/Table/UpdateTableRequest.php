<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role->slug, ['admin', 'manager']);
    }

    public function rules(): array
    {
        $table   = $this->route('table');
        $storeId = $this->user()->store_id ?? $table->store_id;

        return [
            'number'   => [
                'required', 'string', 'max:20',
                Rule::unique('tables', 'number')
                    ->ignore($table->id)
                    ->where('store_id', $storeId),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'section'  => ['nullable', 'string', 'max:50'],
            'status'   => ['required', 'in:available,closed'],
        ];
    }

    public function messages(): array
    {
        return ['number.unique' => 'Nomor meja sudah digunakan.'];
    }
}