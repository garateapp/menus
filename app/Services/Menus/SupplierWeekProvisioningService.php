<?php

namespace App\Services\Menus;

use App\Enums\MenuStatus;
use App\Models\WeeklyMenu;
use Carbon\CarbonImmutable;

class SupplierWeekProvisioningService
{
    public function ensureDraftWeeks(int $supplierId, ?CarbonImmutable $fromDate = null, int $futureWeeks = 52): void
    {
        $startOfWeek = ($fromDate ?? CarbonImmutable::today())->startOfWeek();
        $existingWeekStarts = WeeklyMenu::query()
            ->where('supplier_id', $supplierId)
            ->whereDate('week_start_date', '>=', $startOfWeek->toDateString())
            ->pluck('week_start_date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
            ->all();

        for ($offset = 0; $offset <= $futureWeeks; $offset++) {
            $weekStart = $startOfWeek->addWeeks($offset);

            if (in_array($weekStart->toDateString(), $existingWeekStarts, true)) {
                continue;
            }

            WeeklyMenu::query()->create([
                'supplier_id' => $supplierId,
                'title' => sprintf('Semana N° %s', $weekStart->format('W')),
                'week_start_date' => $weekStart->toDateString(),
                'week_end_date' => $weekStart->endOfWeek()->toDateString(),
                'status' => MenuStatus::Draft,
                'notes' => null,
            ]);
        }
    }
}
