<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Hypervel\Foundation\Http\FormRequest;

class ListUserRequest extends FormRequest
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
            'search'   => 'nullable|string|max:255',
            'role'     => 'nullable|string|max:50',
            'status'   => 'nullable|string|max:50',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
