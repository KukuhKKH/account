<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isSuperadmin() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Password baru harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'reason.required' => 'Alasan reset password harus diisi.',
            'reason.max' => 'Alasan terlalu panjang (maksimal 500 karakter).',
        ];
    }
}
