<?php
namespace App\Http\Requests\Order;

class UpdateOrderRequest extends StoreOrderRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['order_type'], $rules['table_id']);
        $rules['customer_name'] = ['nullable', 'string', 'max:100'];
        return $rules;
    }
}
