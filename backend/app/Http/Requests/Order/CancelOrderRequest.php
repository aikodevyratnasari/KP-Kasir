<?php
namespace App\Http\Requests\Order;
use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager', 'cashier']); }
    public function rules(): array { return ['cancel_reason' => ['required', 'string', 'max:500']]; }
}
