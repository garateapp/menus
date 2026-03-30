<?php

namespace Database\Seeders;

use App\Enums\MenuStatus;
use App\Models\DailyMenu;
use App\Models\MenuOption;
use App\Models\MenuSelection;
use App\Models\User;
use App\Models\WeeklyMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin Demo',
            'email' => 'admin@greenex.cl',
            'username' => 'superadmin',
        ]);
        $superAdmin->assignRole('SuperAdmin');

        $supplier = User::factory()->create([
            'name' => 'Proveedor Demo',
            'email' => 'supplier@greenex.cl',
            'username' => 'supplierdemo',
        ]);
        $supplier->assignRole('Supplier');

        $workers = collect(range(1, 10))
            ->map(function (int $index): User {
                $worker = User::factory()->create([
                    'name' => "Trabajador {$index}",
                    'email' => "worker{$index}@greenex.cl",
                    'username' => "worker{$index}",
                ]);
                $worker->assignRole('Worker');

                return $worker;
            });

        $weekStart = now()->startOfWeek();
        $weeklyMenu = WeeklyMenu::query()->create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana demo '.$weekStart->format('d/m').' - '.$weekStart->copy()->addDays(4)->format('d/m'),
            'week_start_date' => $weekStart->toDateString(),
            'week_end_date' => $weekStart->copy()->addDays(4)->toDateString(),
            'status' => MenuStatus::Published,
            'notes' => 'Semana de ejemplo para pruebas internas.',
        ]);

        foreach (range(0, 4) as $offset) {
            $dailyMenu = DailyMenu::query()->create([
                'weekly_menu_id' => $weeklyMenu->id,
                'menu_date' => $weekStart->copy()->addDays($offset)->toDateString(),
                'status' => MenuStatus::Published,
            ]);

            $options = $this->createOptionsForDay($dailyMenu, $offset);
            $this->createSelectionsForDay($workers, $dailyMenu, $options);
        }
    }

    private function createOptionsForDay(DailyMenu $dailyMenu, int $offset): Collection
    {
        $optionSets = [
            [
                ['title' => 'Pollo al jugo', 'description' => 'Con arroz graneado y ensalada chilena.'],
                ['title' => 'Pasta boloñesa', 'description' => 'Salsa de carne con queso rallado.'],
                ['title' => 'Ensalada césar', 'description' => 'Con proteína de pollo y crutones.'],
            ],
            [
                ['title' => 'Carne al horno', 'description' => 'Con puré casero y verduras salteadas.'],
                ['title' => 'Merluza apanada', 'description' => 'Con papas doradas y limón.'],
            ],
            [
                ['title' => 'Lasaña tradicional', 'description' => 'Con salsa pomodoro y queso gratinado.'],
                ['title' => 'Charquicán', 'description' => 'Con huevo frito y ensalada verde.'],
                ['title' => 'Wrap vegetariano', 'description' => 'Con hummus y verduras frescas.'],
                ['title' => 'Pollo teriyaki', 'description' => 'Con arroz primavera.'],
            ],
            [
                ['title' => 'Porotos con rienda', 'description' => 'Receta casera con pebre.'],
                ['title' => 'Pechuga grillada', 'description' => 'Con quinoa y vegetales.'],
            ],
            [
                ['title' => 'Pastel de papas', 'description' => 'Con carne molida y huevo.'],
                ['title' => 'Pescado a la plancha', 'description' => 'Con ensalada surtida.'],
                ['title' => 'Gnocchi pesto', 'description' => 'Con tomate cherry y parmesano.'],
            ],
        ];

        return collect($optionSets[$offset])->values()->map(
            fn (array $option, int $index) => MenuOption::query()->create([
                'daily_menu_id' => $dailyMenu->id,
                'title' => $option['title'],
                'description' => $option['description'],
                'image_path' => null,
                'quota' => null,
                'is_visible' => true,
                'sort_order' => $index + 1,
            ])
        );
    }

    private function createSelectionsForDay(Collection $workers, DailyMenu $dailyMenu, Collection $options): void
    {
        foreach ($workers as $worker) {
            $selectedOption = $options->random();

            MenuSelection::query()->create([
                'user_id' => $worker->id,
                'daily_menu_id' => $dailyMenu->id,
                'menu_option_id' => $selectedOption->id,
                'selected_at' => now()->subMinutes(random_int(5, 240)),
            ]);
        }
    }
}
