<?php

namespace Database\Factories;

use App\Enums\MenuStatus;
use App\Models\DailyMenu;
use App\Models\WeeklyMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyMenu>
 */
class DailyMenuFactory extends Factory
{
    protected $model = DailyMenu::class;

    public function definition(): array
    {
        return [
            'weekly_menu_id' => WeeklyMenu::factory(),
            'menu_date' => fake()->date(),
            'status' => fake()->randomElement(MenuStatus::cases()),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => MenuStatus::Published,
        ]);
    }
}
