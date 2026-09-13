<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Hypervel\Foundation\Http\FormRequest;

class ChangeUserStatusRequest extends FormRequest
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
            'status' => 'required|string|in:active,suspended',
            'reason' => 'nullable|string|max:255',
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
            'status.required' => 'Status akun pengguna wajib ditentukan.',
            'status.in'       => 'Status akun harus berupa active atau suspended.',
        ];
    }
}
