<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreWeeklyMenuRequest;
use App\Models\DailyMenu;
use App\Models\WeeklyMenu;
use App\Services\Calendar\CalendarRangeService;
use App\Services\Menus\SupplierWeekProvisioningService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WeeklyMenuController extends Controller
{
    public function index(
        Request $request,
        CalendarRangeService $calendarRangeService,
        SupplierWeekProvisioningService $supplierWeekProvisioningService,
    ): Response
    {
        $calendar = $calendarRangeService->build($request->query('view'), $request->query('date'));
        $supplierWeekProvisioningService->ensureDraftWeeks($request->user()->id);

        $weeklyMenus = WeeklyMenu::query()
            ->where('supplier_id', $request->user()->id)
            ->whereDate('week_start_date', '<=', $calendar['range_end'])
            ->whereDate('week_end_date', '>=', $calendar['range_start'])
            ->with([
                'dailyMenus' => fn ($query) => $query
                    ->whereDate('menu_date', '>=', $calendar['range_start'])
                    ->whereDate('menu_date', '<=', $calendar['range_end'])
                    ->orderBy('menu_date'),
                'dailyMenus.menuOptions' => fn ($query) => $query
                    ->withCount('selections')
                    ->orderByDesc('is_opt_out')
                    ->orderBy('sort_order'),
            ])
            ->orderBy('week_start_date')
            ->get();

        $dailyMenuLookup = $this->buildDailyMenuLookup($weeklyMenus);
        $selectedDate = $request->query('selected_date') ?: ($calendar['view'] === 'day' ? $calendar['focus_date'] : null);
        $selectedDateObject = null;
        $selectedWeeklyMenu = null;
        $selectedDailyMenu = null;
        $selectedWeekDefaults = null;

        if ($selectedDate) {
            try {
                $selectedDateObject = CarbonImmutable::parse($selectedDate);
            } catch (\Throwable) {
                $selectedDate = null;
                $selectedDateObject = null;
            }
        }

        if ($selectedDate && $selectedDateObject) {
            $selectedWeekDefaults = $calendarRangeService->weekBounds($selectedDateObject);
            $selectedDailyEntry = $dailyMenuLookup[$selectedDate] ?? null;

            if ($selectedDailyEntry) {
                $selectedWeeklyMenu = $selectedDailyEntry['weekly_menu'];
                $selectedDailyMenu = $selectedDailyEntry['daily_menu'];
            }

            if (! $selectedWeeklyMenu) {
                $selectedWeeklyMenu = $weeklyMenus->first(function (WeeklyMenu $weeklyMenu) use ($selectedDateObject) {
                    return $selectedDateObject->betweenIncluded(
                        CarbonImmutable::parse($weeklyMenu->week_start_date),
                        CarbonImmutable::parse($weeklyMenu->week_end_date),
                    );
                });
            }

            if (! $selectedDailyMenu && $selectedWeeklyMenu) {
                $selectedDailyMenu = $selectedWeeklyMenu->dailyMenus->first(
                    fn (DailyMenu $dailyMenu) => $dailyMenu->menu_date->toDateString() === $selectedDate,
                );
            }
        }

        return Inertia::render('Supplier/WeeklyMenus/Index', [
            'calendar' => [
                ...$calendar,
                'cells' => $this->buildCalendarCells($calendar['days'], $weeklyMenus, $dailyMenuLookup),
            ],
            'selectedDate' => $selectedDate,
            'selectedWeeklyMenu' => $selectedWeeklyMenu,
            'selectedDailyMenu' => $selectedDailyMenu,
            'selectedWeekDefaults' => $selectedWeekDefaults,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Supplier/WeeklyMenus/Create');
    }

    public function store(StoreWeeklyMenuRequest $request): RedirectResponse
    {
        $weeklyMenu = WeeklyMenu::query()->create([
            ...$request->validated(),
            'supplier_id' => $request->user()->id,
        ]);

        if ($redirectResponse = $this->redirectToCalendarIfRequested($request, 'Semana creada correctamente.')) {
            return $redirectResponse;
        }

        return redirect()
            ->route('supplier.weekly-menus.show', $weeklyMenu)
            ->with('success', 'Semana creada correctamente.');
    }

    public function edit(WeeklyMenu $weeklyMenu): Response
    {
        $this->authorize('update', $weeklyMenu);

        return Inertia::render('Supplier/WeeklyMenus/Edit', [
            'weeklyMenu' => $weeklyMenu,
        ]);
    }

    public function show(WeeklyMenu $weeklyMenu): Response
    {
        $this->authorize('view', $weeklyMenu);

        $weeklyMenu->load([
            'dailyMenus' => fn ($query) => $query->orderBy('menu_date'),
            'dailyMenus.menuOptions' => fn ($query) => $query
                ->withCount('selections')
                ->orderByDesc('is_opt_out')
                ->orderBy('sort_order'),
        ]);

        return Inertia::render('Supplier/WeeklyMenus/Show', [
            'weeklyMenu' => $weeklyMenu,
        ]);
    }

    public function update(StoreWeeklyMenuRequest $request, WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->authorize('update', $weeklyMenu);

        $weeklyMenu->update($request->validated());

        if ($redirectResponse = $this->redirectToCalendarIfRequested($request, 'Semana actualizada correctamente.')) {
            return $redirectResponse;
        }

        return redirect()
            ->route('supplier.weekly-menus.show', $weeklyMenu)
            ->with('success', 'Semana actualizada correctamente.');
    }

    private function buildCalendarCells(array $days, $weeklyMenus, array $dailyMenuLookup): array
    {
        return array_map(function (array $day) use ($weeklyMenus, $dailyMenuLookup) {
            $cellDate = CarbonImmutable::parse($day['date']);
            $dailyEntry = $dailyMenuLookup[$day['date']] ?? null;

            /** @var WeeklyMenu|null $weeklyMenu */
            $weeklyMenu = $dailyEntry['weekly_menu'] ?? $weeklyMenus->first(function (WeeklyMenu $candidate) use ($cellDate) {
                return $cellDate->betweenIncluded(
                    CarbonImmutable::parse($candidate->week_start_date),
                    CarbonImmutable::parse($candidate->week_end_date),
                );
            });

            /** @var DailyMenu|null $dailyMenu */
            $dailyMenu = $dailyEntry['daily_menu'] ?? null;

            return [
                ...$day,
                'has_week' => (bool) $weeklyMenu,
                'has_day' => (bool) $dailyMenu,
                'weekly_menu_id' => $weeklyMenu?->id,
                'daily_menu_id' => $dailyMenu?->id,
                'status' => $dailyMenu?->status?->value ?? $weeklyMenu?->status?->value,
                'options_count' => $dailyMenu ? $dailyMenu->menuOptions->where('is_opt_out', false)->count() : 0,
                'title' => $weeklyMenu?->title,
            ];
        }, $days);
    }

    private function buildDailyMenuLookup($weeklyMenus): array
    {
        $lookup = [];

        foreach ($weeklyMenus as $weeklyMenu) {
            foreach ($weeklyMenu->dailyMenus as $dailyMenu) {
                $date = $dailyMenu->menu_date->toDateString();
                $existing = $lookup[$date] ?? null;

                if (! $existing || $this->shouldPrioritizeWeeklyMenu($weeklyMenu, $existing['weekly_menu'])) {
                    $lookup[$date] = [
                        'weekly_menu' => $weeklyMenu,
                        'daily_menu' => $dailyMenu,
                    ];
                }
            }
        }

        return $lookup;
    }

    private function shouldPrioritizeWeeklyMenu(WeeklyMenu $candidate, WeeklyMenu $current): bool
    {
        $candidateLength = $candidate->week_start_date->diffInDays($candidate->week_end_date);
        $currentLength = $current->week_start_date->diffInDays($current->week_end_date);

        if ($candidateLength !== $currentLength) {
            return $candidateLength < $currentLength;
        }

        return $candidate->id > $current->id;
    }

    private function redirectToCalendarIfRequested(Request $request, string $message): ?RedirectResponse
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo === '') {
            return null;
        }

        return redirect()->to($redirectTo)->with('success', $message);
    }
}
