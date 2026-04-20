<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('notes') && $this->has('note')) {
            $this->merge(['notes' => $this->input('note')]);
        }
    }

    public function rules(): array
    {
        return [
            'notes' => 'required|string|max:1000',
        ];
    }
}
