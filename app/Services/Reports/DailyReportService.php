<?php

namespace App\Services\Reports;

use App\Models\DailyMenu;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class DailyReportService
{
    public function buildForSupplier(User $supplier, ?string $date): ?array
    {
        $query = DailyMenu::query()
            ->whereHas('weeklyMenu', fn ($query) => $query->where('supplier_id', $supplier->id));

        return $this->buildReport($query, $date);
    }

    public function buildForSuperAdmin(?string $date): ?array
    {
        return $this->buildReport(DailyMenu::query(), $date);
    }

    public function availableDatesForSupplier(User $supplier): array
    {
        return $this->availableDates(
            DailyMenu::query()->whereHas('weeklyMenu', fn ($query) => $query->where('supplier_id', $supplier->id))
        );
    }

    public function availableDatesForSuperAdmin(): array
    {
        return $this->availableDates(DailyMenu::query());
    }

    private function buildReport(Builder $query, ?string $date): ?array
    {
        $query->with([
            'menuOptions.selections',
            'weeklyMenu',
            'selections.user',
            'selections.menuOption',
        ]);

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
            ])
            ->all();

        $selections = $dailyMenu->selections
            ->sortBy(fn ($selection) => mb_strtolower($selection->user?->name ?? ''))
            ->values()
            ->map(fn ($selection) => [
                'id' => $selection->id,
                'userName' => $selection->user?->name,
                'userEmail' => $selection->user?->email,
                'username' => $selection->user?->username,
                'optionTitle' => $selection->menuOption?->title,
                'selectedAt' => $selection->selected_at?->toIso8601String(),
            ])
            ->all();

        $selectedUserIds = $dailyMenu->selections
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->all();

        $workerRoleExists = Role::query()
            ->where('name', 'Worker')
            ->where('guard_name', 'web')
            ->exists();

        $noResponses = $workerRoleExists
            ? User::query()
                ->role('Worker')
                ->where('is_active', true)
                ->when($selectedUserIds !== [], fn (Builder $builder) => $builder->whereNotIn('id', $selectedUserIds))
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'username'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'userName' => $user->name,
                    'userEmail' => $user->email,
                    'username' => $user->username,
                ])
                ->all()
            : [];

        return [
            'date' => $dailyMenu->menu_date?->toDateString(),
            'weekTitle' => $dailyMenu->weeklyMenu->title,
            'totalSelections' => count($selections),
            'options' => $options,
            'selections' => $selections,
            'noResponses' => $noResponses,
        ];
    }

    private function availableDates(Builder $query): array
    {
        return $query
            ->orderByDesc('menu_date')
            ->pluck('menu_date')
            ->map(fn ($date) => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date)
            ->unique()
            ->values()
            ->all();
    }
}
