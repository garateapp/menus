<?php

namespace App\Services\Reports;

use App\Models\User;
use App\Models\WeeklyMenu;

class WeeklyReportService
{
    public function buildForSupplier(User $supplier, mixed $weeklyMenuId): ?array
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
            ]);

        return [
            'weeklyMenuId' => $weeklyMenu->id,
            'title' => $weeklyMenu->title,
            'status' => $weeklyMenu->status->value,
            'totalSelections' => $weeklyMenu->dailyMenus->sum(fn ($dailyMenu) => $dailyMenu->selections()->count()),
            'days' => $days,
        ];
    }
}
