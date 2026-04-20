<?php

namespace App\Http\Requests\Governorates;

use Illuminate\Foundation\Http\FormRequest;

class StoreGovernorateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:governorates,name',
            'shipping_cost' => 'required|numeric|min:0',
        ];
    }
}
