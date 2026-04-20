<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->user()?->id;

        return [
            'image_path' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|min:10|max:20|regex:/^[0-9+\\-\\s]+$/',
            'current_password' => 'required_with:password|current_password',
            'password' => 'nullable|sometimes|string|min:8|confirmed',
            'role_id' => 'sometimes|exists:roles,id',
        ];
    }
}
