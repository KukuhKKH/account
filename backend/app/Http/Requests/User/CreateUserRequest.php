<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\UserRole;
use Hypervel\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        $validRoles = implode(',', UserRole::getAllRoles());

        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'role'     => 'required|string|in:' . $validRoles,
            'password' => 'nullable|string|min:8|max:128',
            'phone'    => 'nullable|string|max:30',
            'address'  => 'nullable|string|max:500',
            'avatar'   => 'nullable|string|max:500',
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'Nama lengkap pengguna wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format alamat email tidak valid.',
            'role.required'     => 'Role hak akses pengguna wajib dipilih.',
            'role.in'           => 'Role yang dipilih tidak valid dalam ekosistem.',
            'password.min'      => 'Panjang kata sandi minimal 8 karakter.',
        ];
    }
}
