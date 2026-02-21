<?php
namespace App\Http\Requests\Payment;
use Illuminate\Foundation\Http\FormRequest;

class RefundPaymentRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        return ['refund_amount' => ['required', 'numeric', 'min:0.01'], 'refund_reason' => ['required', 'string', 'max:500']];
    }
}
