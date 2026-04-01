<?php

namespace App\Http\Controllers\Worker;

use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();

        $todayMenu = DailyMenu::query()
            ->whereDate('menu_date', today())
            ->where('status', MenuStatus::Published)
            ->whereHas('weeklyMenu', fn ($query) => $query->where('status', '!=', MenuStatus::Closed))
            ->with([
                'menuOptions' => fn ($query) => $query
                    ->where('is_visible', true)
                    ->withCount('selections')
                    ->orderByDesc('is_opt_out')
                    ->orderBy('sort_order'),
                'selections' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->first();

        $upcomingMenus = DailyMenu::query()
            ->whereDate('menu_date', '>=', today())
            ->where('status', MenuStatus::Published)
            ->whereHas('weeklyMenu', fn ($query) => $query->where('status', '!=', MenuStatus::Closed))
            ->with(['weeklyMenu', 'menuOptions' => fn ($query) => $query
                ->where('is_visible', true)
                ->orderByDesc('is_opt_out')
                ->orderBy('sort_order')])
            ->orderBy('menu_date')
            ->limit(5)
            ->get();

        $recentSelections = $user->menuSelections()
            ->with(['dailyMenu.weeklyMenu', 'menuOption'])
            ->latest('selected_at')
            ->limit(5)
            ->get();

        return Inertia::render('Worker/Dashboard', [
            'todayMenu' => $todayMenu,
            'upcomingMenus' => $upcomingMenus,
            'recentSelections' => $recentSelections,
        ]);
    }
}
