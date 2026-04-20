<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ListUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'nullable|integer|min:1|max:50',
            'search' => 'nullable|string',
            'role_id' => 'nullable|integer|exists:roles,id',
            'is_banned' => 'nullable|boolean',
        ];
    }
}
