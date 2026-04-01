<?php

namespace App\Services\Menus;

use App\Models\DailyMenu;
use App\Models\MenuOption;

class OptOutMenuOptionService
{
    public const TITLE = 'No solicitaré menú';

    public function ensureForDailyMenu(DailyMenu|int $dailyMenu): void
    {
        $dailyMenuId = $dailyMenu instanceof DailyMenu ? $dailyMenu->id : $dailyMenu;

        $hasRegularOptions = MenuOption::query()
            ->where('daily_menu_id', $dailyMenuId)
            ->where('is_opt_out', false)
            ->exists();

        if (! $hasRegularOptions) {
            return;
        }

        MenuOption::query()->updateOrCreate(
            [
                'daily_menu_id' => $dailyMenuId,
                'is_opt_out' => true,
            ],
            [
                'title' => self::TITLE,
                'description' => 'Opción automática del sistema para informar que no solicitarás almuerzo este día.',
                'image_path' => null,
                'quota' => null,
                'is_visible' => true,
                'sort_order' => 0,
            ],
        );
    }
}
