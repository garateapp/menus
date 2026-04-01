<?php

namespace App\Http\Controllers\Worker;

use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use App\Services\Calendar\CalendarRangeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(Request $request, CalendarRangeService $calendarRangeService): Response
    {
        $user = $request->user();
        $calendar = $calendarRangeService->build($request->query('view'), $request->query('date'));

        $menus = DailyMenu::query()
            ->whereDate('menu_date', '>=', $calendar['range_start'])
            ->whereDate('menu_date', '<=', $calendar['range_end'])
            ->where('status', MenuStatus::Published)
            ->whereHas('weeklyMenu', fn ($query) => $query->where('status', '!=', MenuStatus::Closed))
            ->with([
                'weeklyMenu',
                'menuOptions' => fn ($query) => $query
                    ->where('is_visible', true)
                    ->withCount('selections')
                    ->orderByDesc('is_opt_out')
                    ->orderBy('sort_order'),
                'selections' => fn ($query) => $query->where('user_id', $user->id)->with('menuOption'),
            ])
            ->orderBy('menu_date')
            ->get();

        $menusByDate = $menus->keyBy(fn (DailyMenu $menu) => $menu->menu_date->toDateString());
        $selectedDate = $request->query('selected_date') ?: ($calendar['view'] === 'day' ? $calendar['focus_date'] : null);
        $selectedMenu = $selectedDate ? $menusByDate->get($selectedDate) : null;

        return Inertia::render('Worker/Menus/Index', [
            'calendar' => [
                ...$calendar,
                'cells' => $this->buildCalendarCells($calendar['days'], $menusByDate),
            ],
            'selectedDate' => $selectedDate,
            'selectedMenu' => $selectedMenu,
        ]);
    }

    public function show(DailyMenu $dailyMenu): Response
    {
        $user = request()->user();

        $dailyMenu->load([
            'weeklyMenu',
            'menuOptions' => fn ($query) => $query
                ->where('is_visible', true)
                ->withCount('selections')
                ->orderByDesc('is_opt_out')
                ->orderBy('sort_order'),
            'selections' => fn ($query) => $query->where('user_id', $user->id)->with('menuOption'),
        ]);

        return Inertia::render('Worker/Menus/Show', [
            'menu' => $dailyMenu,
            'currentSelection' => $dailyMenu->selections->first(),
        ]);
    }

    private function buildCalendarCells(array $days, $menusByDate): array
    {
        return array_map(function (array $day) use ($menusByDate) {
            /** @var DailyMenu|null $menu */
            $menu = $menusByDate->get($day['date']);
            $selection = $menu?->selections->first();

            return [
                ...$day,
                'has_day' => (bool) $menu,
                'daily_menu_id' => $menu?->id,
                'status' => $menu?->status?->value,
                'options_count' => $menu ? $menu->menuOptions->where('is_opt_out', false)->count() : 0,
                'has_selection' => (bool) $selection,
                'selection_title' => $selection?->menuOption?->title,
                'can_interact' => $menu?->status === MenuStatus::Published,
            ];
        }, $days);
    }
}
