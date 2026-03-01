<?php

namespace App\Http\Requests;

use App\Models\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request untuk create user.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canManageUsers();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'address'  => ['nullable', 'string', 'max:500'],
            'avatar'   => ['nullable', 'url', 'max:500'],
            'role'     => ['required', Rule::in(UserRole::getAllRoles())],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user          = auth()->user();
            $requestedRole = $this->input('role');

            if ($user->isAdmin() && !$user->isSuperadmin() && $requestedRole === UserRole::ROLE_SUPERADMIN) {
                $validator->errors()->add('role', 'Anda tidak memiliki izin untuk membuat user dengan role Superadmin.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'Nama user harus diisi.',
            'email.required'    => 'Email harus diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password harus diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'role.required'     => 'Role harus dipilih.',
            'role.in'           => 'Role tidak valid.',
        ];
    }
}
