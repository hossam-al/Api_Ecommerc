<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mega = 2 * 1024 * 1024;

        return [
            'image_path' => "file|max:$mega|nullable",
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:10|max:20|regex:/^[0-9+\\-\\s]+$/|unique:users,phone',
            'password' => 'required|confirmed',
            'role_id' => 'sometimes|exists:roles,id',
        ];
    }
}
