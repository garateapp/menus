<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'not_in:No solicitaré menú'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'is_visible' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.not_in' => 'La opción "No solicitaré menú" es administrada automáticamente por el sistema.',
        ];
    }
}
