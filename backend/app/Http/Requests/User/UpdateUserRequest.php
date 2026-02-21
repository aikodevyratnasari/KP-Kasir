<?php
namespace App\Http\Requests\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->isAdmin(); }
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'role_id'  => ['required', 'exists:roles,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'status'   => ['required', 'in:active,inactive'],
        ];
    }
}
