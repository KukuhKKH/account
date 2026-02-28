<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request untuk update user.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        // Admin bisa update user apapun
        if (auth()->user()->canManageUsers()) {
            return true;
        }

        // User regular hanya bisa update diri sendiri
        return auth()->id() === $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'url', 'max:500'],
        ];

        // Admin bisa update role, user regular tidak
        if (auth()->user()->canManageUsers()) {
            $rules['role'] = ['required', Rule::in(['superadmin', 'admin', 'user'])];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'role.in' => 'Role tidak valid.',
        ];
    }
}
