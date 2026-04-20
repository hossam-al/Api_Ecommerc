<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class RejectProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->reason)) {
            $this->merge([
                'reason' => trim($this->reason),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:5|max:2000',
        ];
    }
}
