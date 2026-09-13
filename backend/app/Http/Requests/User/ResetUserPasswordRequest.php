<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Hypervel\Foundation\Http\FormRequest;

class ResetUserPasswordRequest extends FormRequest
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
        return [
            'password' => 'required|string|min:8|max:255',
            'reason'   => 'nullable|string|max:255',
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
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min'      => 'Kata sandi baru minimal terdiri dari 8 karakter.',
            'reason.max'        => 'Alasan pembaruan kata sandi tidak boleh melebihi 255 karakter.',
        ];
    }
}
