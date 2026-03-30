<?php

namespace Database\Factories;

use App\Enums\MenuStatus;
use App\Models\User;
use App\Models\WeeklyMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WeeklyMenu>
 */
class WeeklyMenuFactory extends Factory
{
    protected $model = WeeklyMenu::class;

    public function definition(): array
    {
        $weekStart = fake()->dateTimeBetween('monday this week', 'monday next week');

        return [
            'supplier_id' => User::factory(),
            'title' => 'Menu semana '.$weekStart->format('W'),
            'week_start_date' => $weekStart->format('Y-m-d'),
            'week_end_date' => $weekStart->modify('+4 days')->format('Y-m-d'),
            'status' => fake()->randomElement(MenuStatus::cases()),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => MenuStatus::Published,
        ]);
    }
}
