<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        // Ambil ID dari route — gunakan ->getKey() agar aman
        $userId = $this->route('user')->getKey();

        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')
                    ->ignore($userId)
                    ->whereNull('deleted_at'),
            ],
            'role_id'  => ['required', 'exists:roles,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'status'   => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'    => 'Email sudah digunakan oleh user lain.',
            'role_id.exists'  => 'Role tidak valid.',
            'store_id.exists' => 'Store tidak valid.',
        ];
    }
}