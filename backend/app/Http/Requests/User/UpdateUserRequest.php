<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\UserRole;
use Hypervel\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name'    => 'nullable|string|max:255',
            'email'   => 'nullable|email|max:255',
            'role'    => 'nullable|string|in:' . $validRoles,
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'avatar'  => 'nullable|string|max:500',
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
            'email.email' => 'Format alamat email tidak valid.',
            'role.in'     => 'Role yang dipilih tidak valid dalam ekosistem.',
        ];
    }
}
