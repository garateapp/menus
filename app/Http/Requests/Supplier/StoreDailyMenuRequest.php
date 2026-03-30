<?php

namespace App\Http\Requests\Supplier;

use App\Enums\MenuStatus;
use App\Models\DailyMenu;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDailyMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $weeklyMenu = $this->route('weeklyMenu') ?? $this->route('dailyMenu')?->weeklyMenu;
        $currentDailyMenu = $this->route('dailyMenu');

        return [
            'menu_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($weeklyMenu, $currentDailyMenu): void {
                    if (! $weeklyMenu) {
                        return;
                    }

                    $duplicateQuery = DailyMenu::query()
                        ->where('weekly_menu_id', $weeklyMenu->id)
                        ->whereDate('menu_date', Carbon::parse($value)->toDateString());

                    if ($currentDailyMenu) {
                        $duplicateQuery->whereKeyNot($currentDailyMenu->id);
                    }

                    if ($duplicateQuery->exists()) {
                        $fail('Ya existe un día configurado para esa fecha dentro de esta semana.');
                    }
                },
            ],
            'status' => ['required', new Enum(MenuStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('menu_date')) {
            return;
        }

        $this->merge([
            'menu_date' => Carbon::parse($this->input('menu_date'))->toDateString(),
        ]);
    }
}
