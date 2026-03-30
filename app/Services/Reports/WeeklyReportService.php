<?php

namespace App\Services\Reports;

use App\Models\User;
use App\Models\WeeklyMenu;

class WeeklyReportService
{
    public function buildForSupplier(User $supplier, mixed $weeklyMenuId, ?string $selectedDay = null): ?array
    {
        $query = WeeklyMenu::query()
            ->where('supplier_id', $supplier->id)
            ->with(['dailyMenus.menuOptions.selections']);

        if ($weeklyMenuId) {
            $query->whereKey((int) $weeklyMenuId);
        } else {
            $query->orderByDesc('week_start_date');
        }

        $weeklyMenu = $query->first();

        if (! $weeklyMenu) {
            return null;
        }

        $days = $weeklyMenu->dailyMenus
            ->sortBy('menu_date')
            ->values()
            ->map(fn ($dailyMenu) => [
                'id' => $dailyMenu->id,
                'menuDate' => $dailyMenu->menu_date?->toDateString(),
                'status' => $dailyMenu->status->value,
                'totalSelections' => $dailyMenu->selections()->count(),
                'options' => $dailyMenu->menuOptions
                    ->sortBy('sort_order')
                    ->values()
                    ->map(fn ($option) => [
                        'id' => $option->id,
                        'title' => $option->title,
                        'description' => $option->description,
                        'totalSelections' => $option->selections->count(),
                    ]),
            ]);

        $selectedDayMenu = null;

        if ($selectedDay) {
            $selectedDayMenu = $weeklyMenu->dailyMenus
                ->first(fn ($dailyMenu) => $dailyMenu->menu_date?->toDateString() === $selectedDay);
        }

        if (! $selectedDayMenu) {
            $selectedDayMenu = $weeklyMenu->dailyMenus
                ->sortBy('menu_date')
                ->first();
        }

        $selectedDayReport = null;

        if ($selectedDayMenu) {
            $selectedDayReport = [
                'date' => $selectedDayMenu->menu_date?->toDateString(),
                'status' => $selectedDayMenu->status->value,
                'totalSelections' => $selectedDayMenu->selections()->count(),
                'options' => $selectedDayMenu->menuOptions
                    ->sortBy('sort_order')
                    ->values()
                    ->map(fn ($option) => [
                        'id' => $option->id,
                        'title' => $option->title,
                        'description' => $option->description,
                        'totalSelections' => $option->selections->count(),
                    ]),
            ];
        }

        return [
            'weeklyMenuId' => $weeklyMenu->id,
            'title' => $weeklyMenu->title,
            'status' => $weeklyMenu->status->value,
            'totalSelections' => $weeklyMenu->dailyMenus->sum(fn ($dailyMenu) => $dailyMenu->selections()->count()),
            'days' => $days,
            'selectedDay' => $selectedDayReport,
        ];
    }

    public function availableWeeksForSupplier(User $supplier): array
    {
        return WeeklyMenu::query()
            ->where('supplier_id', $supplier->id)
            ->orderByDesc('week_start_date')
            ->get(['id', 'title', 'week_start_date', 'week_end_date'])
            ->map(fn ($weeklyMenu) => [
                'id' => $weeklyMenu->id,
                'title' => $weeklyMenu->title,
                'weekStartDate' => $weeklyMenu->week_start_date?->toDateString(),
                'weekEndDate' => $weeklyMenu->week_end_date?->toDateString(),
            ])
            ->all();
    }
}
