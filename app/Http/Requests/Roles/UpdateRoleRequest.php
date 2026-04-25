<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->route('role') ?? $this->route('id');
        $roleId = is_object($role) ? $role->id : $role;

        return [
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $roleId,
            'description' => 'nullable|string',
        ];
    }
}
