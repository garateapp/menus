<?php

namespace App\Http\Controllers\Supplier;

use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use App\Models\WeeklyMenu;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $supplier = request()->user();
        $today = today()->toDateString();
        $baseQuery = WeeklyMenu::query()
            ->where('supplier_id', $supplier->id)
            ->whereIn('status', [MenuStatus::Draft, MenuStatus::Published]);

        $activeWeek = (clone $baseQuery)
            ->whereDate('week_start_date', '<=', $today)
            ->whereDate('week_end_date', '>=', $today)
            ->with(['dailyMenus.menuOptions.selections'])
            ->orderByRaw("case when status = ? then 0 else 1 end", [MenuStatus::Published->value])
            ->orderBy('week_start_date')
            ->first();

        if (! $activeWeek) {
            $activeWeek = (clone $baseQuery)
                ->whereDate('week_start_date', '>=', $today)
                ->with(['dailyMenus.menuOptions.selections'])
                ->orderBy('week_start_date')
                ->first();
        }

        $todaySelections = DailyMenu::query()
            ->whereDate('menu_date', today())
            ->whereHas('weeklyMenu', fn ($query) => $query->where('supplier_id', $supplier->id))
            ->withCount('selections')
            ->get()
            ->sum('selections_count');

        return Inertia::render('Supplier/Dashboard', [
            'activeWeek' => $activeWeek,
            'stats' => [
                'configuredDays' => $activeWeek?->dailyMenus?->count() ?? 0,
                'todaySelections' => $todaySelections,
                'visibleOptions' => $activeWeek?->dailyMenus?->sum(fn ($day) => $day->menuOptions->where('is_visible', true)->count()) ?? 0,
            ],
        ]);
    }
}
