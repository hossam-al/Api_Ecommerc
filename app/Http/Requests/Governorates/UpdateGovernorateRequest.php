<?php

namespace App\Http\Requests\Governorates;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGovernorateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('governorate');

        return [
            'name' => 'sometimes|required|string|unique:governorates,name,' . $id,
            'shipping_cost' => 'sometimes|required|numeric|min:0',
        ];
    }
}
