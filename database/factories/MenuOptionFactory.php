<?php

namespace Database\Factories;

use App\Models\DailyMenu;
use App\Models\MenuOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuOption>
 */
class MenuOptionFactory extends Factory
{
    protected $model = MenuOption::class;

    public function definition(): array
    {
        return [
            'daily_menu_id' => DailyMenu::factory(),
            'title' => fake()->randomElement([
                'Pollo al horno',
                'Pasta primavera',
                'Carne mechada',
                'Ensalada proteica',
                'Pescado a la plancha',
            ]),
            'description' => fake()->sentence(),
            'image_path' => null,
            'quota' => fake()->optional()->numberBetween(20, 80),
            'is_visible' => true,
            'sort_order' => 0,
        ];
    }
}
