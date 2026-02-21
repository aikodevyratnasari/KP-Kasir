<?php
namespace App\Http\Requests\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager', 'cashier']); }
    public function rules(): array
    {
        return [
            'table_id'       => ['required', 'exists:tables,id'],
            'customer_name'  => ['required', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'reserved_at'    => ['required', 'date', 'after:now'],
            'guest_count'    => ['nullable', 'integer', 'min:1'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }
    public function messages(): array { return ['reserved_at.after' => 'Waktu reservasi harus di masa depan.']; }
}
