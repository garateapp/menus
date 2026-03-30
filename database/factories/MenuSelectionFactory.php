<?php

namespace Database\Factories;

use App\Models\DailyMenu;
use App\Models\MenuOption;
use App\Models\MenuSelection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuSelection>
 */
class MenuSelectionFactory extends Factory
{
    protected $model = MenuSelection::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'daily_menu_id' => DailyMenu::factory(),
            'menu_option_id' => MenuOption::factory(),
            'selected_at' => fake()->dateTimeBetween('-5 days', 'now'),
        ];
    }
}
