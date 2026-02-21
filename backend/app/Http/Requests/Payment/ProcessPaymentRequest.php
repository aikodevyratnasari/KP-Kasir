<?php
namespace App\Http\Requests\Payment;
use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager', 'cashier']); }
    public function rules(): array
    {
        return [
            'payment_method'   => ['required', 'in:cash,card,ewallet'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'amount_received'  => ['required_if:payment_method,cash', 'nullable', 'numeric'],
            'card_type'        => ['required_if:payment_method,card', 'nullable', 'in:Visa,Mastercard'],
            'card_last_four'   => ['required_if:payment_method,card', 'nullable', 'digits:4'],
            'approval_code'    => ['required_if:payment_method,card', 'nullable', 'string', 'max:50'],
            'ewallet_type'     => ['required_if:payment_method,ewallet', 'nullable', 'in:GoPay,OVO,Dana,ShopeePay'],
            'reference_number' => ['required_if:payment_method,ewallet', 'nullable', 'string', 'max:100'],
        ];
    }
}
