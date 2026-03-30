<?php

namespace App\Http\Requests\Supplier;

use App\Enums\MenuStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreWeeklyMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'week_start_date' => ['required', 'date'],
            'week_end_date' => ['required', 'date', 'after_or_equal:week_start_date'],
            'status' => ['required', new Enum(MenuStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
