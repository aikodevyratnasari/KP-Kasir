<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            // ignore soft-deleted users — email mereka boleh dipakai lagi
            'email'    => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'role_id'  => ['required', 'exists:roles,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'status'   => ['sometimes', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'    => 'Email sudah digunakan.',
            'role_id.exists'  => 'Role tidak valid.',
            'store_id.exists' => 'Store tidak valid.',
        ];
    }
}