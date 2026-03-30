<?php

namespace App\Services\Reports;

use App\Models\DailyMenu;
use App\Models\User;

class DailyReportService
{
    public function buildForSupplier(User $supplier, ?string $date): ?array
    {
        $query = DailyMenu::query()
            ->whereHas('weeklyMenu', fn ($query) => $query->where('supplier_id', $supplier->id))
            ->with(['menuOptions.selections', 'weeklyMenu']);

        if ($date) {
            $query->whereDate('menu_date', $date);
        } else {
            $query->orderByDesc('menu_date');
        }

        $dailyMenu = $query->first();

        if (! $dailyMenu) {
            return null;
        }

        $options = $dailyMenu->menuOptions
            ->sortBy('sort_order')
            ->values()
            ->map(fn ($option) => [
                'id' => $option->id,
                'title' => $option->title,
                'description' => $option->description,
                'totalSelections' => $option->selections->count(),
            ]);

        return [
            'date' => $dailyMenu->menu_date?->toDateString(),
            'weekTitle' => $dailyMenu->weeklyMenu->title,
            'totalSelections' => $dailyMenu->selections()->count(),
            'options' => $options,
        ];
    }
}
