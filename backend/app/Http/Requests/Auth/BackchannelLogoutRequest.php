<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Hypervel\Foundation\Http\FormRequest;

class BackchannelLogoutRequest extends FormRequest
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
            'logout_token' => 'required|string',
        ];
    }
}
