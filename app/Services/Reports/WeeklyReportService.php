<?php

namespace App\Services\Reports;

use App\Models\MenuOption;
use App\Models\User;
use App\Models\WeeklyMenu;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class WeeklyReportService
{
    public function buildForSupplier(User $supplier, mixed $weeklyMenuId, ?string $selectedDay = null): ?array
    {
        $query = WeeklyMenu::query()
            ->where('supplier_id', $supplier->id)
            ->with([
                'dailyMenus.menuOptions.selections',
                'dailyMenus.selections.user',
            ]);

        if ($weeklyMenuId) {
            $query->whereKey((int) $weeklyMenuId);
        } else {
            $query->orderByDesc('week_start_date');
        }

        $weeklyMenu = $query->first();

        if (! $weeklyMenu) {
            return null;
        }

        $workerRoleExists = Role::query()
            ->where('name', 'Worker')
            ->where('guard_name', 'web')
            ->exists();

        $workerBaseQuery = $workerRoleExists
            ? User::query()->role('Worker')->where('is_active', true)
            : null;

        $days = $weeklyMenu->dailyMenus
            ->sortBy('menu_date')
            ->values()
            ->map(function ($dailyMenu) use ($workerBaseQuery) {
                $noResponses = $this->buildNoResponses($dailyMenu->selections, $workerBaseQuery);

                return [
                    'id' => $dailyMenu->id,
                    'menuDate' => $dailyMenu->menu_date?->toDateString(),
                    'status' => $dailyMenu->status->value,
                    'totalSelections' => $dailyMenu->selections->count(),
                    'options' => $dailyMenu->menuOptions
                        ->sortBy(fn (MenuOption $option) => [$option->sort_order, $option->title])
                        ->values()
                        ->map(fn ($option) => [
                            'id' => $option->id,
                            'title' => $option->title,
                            'description' => $option->description,
                            'totalSelections' => $option->selections->count(),
                        ])
                        ->all(),
                    'noResponsesCount' => count($noResponses),
                    'noResponses' => $noResponses,
                ];
            })
            ->all();

        $summaryRows = $weeklyMenu->dailyMenus
            ->sortBy('menu_date')
            ->values()
            ->flatMap(function ($dailyMenu) use ($days) {
                $day = collect($days)->firstWhere('id', $dailyMenu->id);

                $selectionRows = $dailyMenu->selections
                    ->sortBy(fn ($selection) => mb_strtolower($selection->user?->name ?? ''))
                    ->values()
                    ->map(fn ($selection) => [
                        'menuDate' => $dailyMenu->menu_date?->toDateString(),
                        'status' => $dailyMenu->status->value,
                        'userName' => $selection->user?->name,
                        'userEmail' => $selection->user?->email,
                        'username' => $selection->user?->username,
                        'optionTitle' => $selection->menuOption?->title,
                        'responseStatus' => 'Seleccionado',
                    ]);

                $noResponseRows = collect($day['noResponses'] ?? [])
                    ->map(fn (array $user) => [
                        'menuDate' => $dailyMenu->menu_date?->toDateString(),
                        'status' => $dailyMenu->status->value,
                        'userName' => $user['userName'],
                        'userEmail' => $user['userEmail'],
                        'username' => $user['username'],
                        'optionTitle' => 'Sin respuesta',
                        'responseStatus' => 'Sin respuesta',
                    ]);

                return $selectionRows->concat($noResponseRows);
            })
            ->values()
            ->all();

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
            $selectedDay = collect($days)->firstWhere('id', $selectedDayMenu->id);

            $selectedDayReport = [
                'date' => $selectedDayMenu->menu_date?->toDateString(),
                'status' => $selectedDayMenu->status->value,
                'totalSelections' => $selectedDayMenu->selections->count(),
                'options' => $selectedDay['options'] ?? [],
                'noResponsesCount' => $selectedDay['noResponsesCount'] ?? 0,
                'noResponses' => $selectedDay['noResponses'] ?? [],
            ];
        }

        return [
            'weeklyMenuId' => $weeklyMenu->id,
            'title' => $weeklyMenu->title,
            'status' => $weeklyMenu->status->value,
            'totalSelections' => $weeklyMenu->dailyMenus->sum(fn ($dailyMenu) => $dailyMenu->selections->count()),
            'days' => $days,
            'summaryRows' => $summaryRows,
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

    private function buildNoResponses(Collection $selections, ?\Illuminate\Database\Eloquent\Builder $workerBaseQuery): array
    {
        if (! $workerBaseQuery) {
            return [];
        }

        $selectedUserIds = $selections
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->all();

        return (clone $workerBaseQuery)
            ->when($selectedUserIds !== [], fn ($query) => $query->whereNotIn('id', $selectedUserIds))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'username'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'userName' => $user->name,
                'userEmail' => $user->email,
                'username' => $user->username,
            ])
            ->all();
    }
}
