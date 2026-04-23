<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role->slug, ['admin', 'manager', 'cashier']);
    }

    public function rules(): array
    {
        return [
            // Cash, kartu — gateway (qris/ewallet/bank_transfer) lewat initiate(), bukan sini
            // Tapi bank_transfer ditambahkan agar tidak error jika form di-submit langsung
            'payment_method'   => ['required', 'in:cash,card,ewallet,qris,bank_transfer'],

            'amount'           => ['required', 'numeric', 'min:0.01'],

            // Cash
            'amount_received'  => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0'],

            // Kartu via EDC
            'card_type'        => ['required_if:payment_method,card', 'nullable', 'in:Visa,Mastercard'],
            'card_last_four'   => ['required_if:payment_method,card', 'nullable', 'digits:4'],
            'approval_code'    => ['required_if:payment_method,card', 'nullable', 'string', 'max:50'],

            // E-Wallet manual
            'ewallet_type'     => ['required_if:payment_method,ewallet', 'nullable', 'in:GoPay,OVO,Dana,ShopeePay'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in'       => 'Metode pembayaran tidak valid.',
            'amount.required'         => 'Jumlah pembayaran wajib diisi.',
            'amount.min'              => 'Jumlah pembayaran harus lebih dari 0.',
            'amount_received.required_if'    => 'Jumlah uang yang diterima wajib diisi untuk pembayaran tunai.',
            'card_type.required_if'          => 'Jenis kartu wajib dipilih.',
            'card_last_four.required_if'     => '4 digit terakhir kartu wajib diisi.',
            'card_last_four.digits'          => '4 digit terakhir harus berupa 4 angka.',
            'approval_code.required_if'      => 'Kode persetujuan dari EDC wajib diisi.',
            'ewallet_type.required_if'       => 'Pilih platform e-wallet.',
            'ewallet_type.in'                => 'Platform e-wallet tidak valid.',
        ];
    }
}