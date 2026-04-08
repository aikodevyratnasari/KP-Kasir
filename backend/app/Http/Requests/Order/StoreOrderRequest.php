<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role->slug, ['admin', 'manager', 'cashier']);
    }

    public function rules(): array
    {
        return [
            'order_type'              => ['required', 'in:dine_in,takeaway'],
            'table_id'                => ['required_if:order_type,dine_in', 'nullable', 'exists:tables,id'],
            'customer_name'           => ['nullable', 'string', 'max:100'],
            'notes'                   => ['nullable', 'string', 'max:500'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'exists:products,id'],
            'items.*.variant_id'      => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
            'items.*.special_notes'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'table_id.required_if' => 'Nomor meja wajib diisi untuk pesanan Dine-In.',
            'items.min'            => 'Pesanan harus mengandung minimal 1 item.',
        ];
    }
}