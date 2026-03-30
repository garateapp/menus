<?php

namespace App\Services\Menus;

use App\Enums\MenuStatus;
use App\Exceptions\InsufficientQuotaException;
use App\Models\DailyMenu;
use App\Models\MenuOption;
use App\Models\MenuSelection;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class MenuSelectionService
{
    public function select(User $user, DailyMenu $dailyMenu, MenuOption $menuOption): MenuSelection
    {
        $dailyMenu->loadMissing('weeklyMenu');

        if (! $user->isActive()) {
            throw new AuthorizationException('Tu cuenta está inactiva.');
        }

        if ($dailyMenu->weeklyMenu->status !== MenuStatus::Published) {
            throw new AuthorizationException('La semana no está publicada.');
        }

        if ($dailyMenu->weeklyMenu->status === MenuStatus::Closed) {
            throw new AuthorizationException('La semana está cerrada.');
        }

        if ($dailyMenu->status !== MenuStatus::Published) {
            throw new AuthorizationException('El día no está publicado.');
        }

        if ($menuOption->daily_menu_id !== $dailyMenu->id || ! $menuOption->is_visible) {
            throw new AuthorizationException('La opción no está disponible.');
        }

        return DB::transaction(function () use ($user, $dailyMenu, $menuOption): MenuSelection {
            $lockedOption = MenuOption::query()
                ->whereKey($menuOption->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingSelection = MenuSelection::query()
                ->where('user_id', $user->id)
                ->where('daily_menu_id', $dailyMenu->id)
                ->lockForUpdate()
                ->first();

            $isSameOption = $existingSelection?->menu_option_id === $lockedOption->id;

            if (! $isSameOption && $lockedOption->quota !== null) {
                $takenSeats = MenuSelection::query()
                    ->where('menu_option_id', $lockedOption->id)
                    ->count();

                if ($takenSeats >= $lockedOption->quota) {
                    throw new InsufficientQuotaException('No quedan cupos disponibles para esta alternativa.');
                }
            }

            if ($existingSelection) {
                $existingSelection->forceFill([
                    'menu_option_id' => $lockedOption->id,
                    'selected_at' => now(),
                ])->save();

                return $existingSelection->refresh();
            }

            return MenuSelection::query()->create([
                'user_id' => $user->id,
                'daily_menu_id' => $dailyMenu->id,
                'menu_option_id' => $lockedOption->id,
                'selected_at' => now(),
            ]);
        });
    }
}
