<?php

namespace Tests\Feature\Database;

use App\Models\DailyMenu;
use App\Models\MenuSelection;
use App\Models\User;
use App\Models\WeeklyMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_roles_permissions_and_demo_data(): void
    {
        $this->seed();

        $this->assertEqualsCanonicalizing(
            ['SuperAdmin', 'Supplier', 'Worker'],
            Role::query()->pluck('name')->all(),
        );

        $this->assertEqualsCanonicalizing(
            [
                'manage users',
                'manage weekly menus',
                'manage daily menus',
                'manage menu options',
                'view reports',
                'select menu option',
                'view own selections',
            ],
            Permission::query()->pluck('name')->all(),
        );

        $this->assertSame(12, User::query()->count());
        $this->assertSame(10, User::role('Worker')->count());
        $this->assertSame(1, User::role('Supplier')->count());
        $this->assertSame(1, User::role('SuperAdmin')->count());

        $this->assertDatabaseHas('users', [
            'email' => 'admin@greenex.cl',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'supplier@greenex.cl',
            'is_active' => true,
        ]);

        $weeklyMenu = WeeklyMenu::query()->first();

        $this->assertNotNull($weeklyMenu);
        $this->assertSame(1, WeeklyMenu::query()->count());
        $this->assertSame(5, DailyMenu::query()->count());
        $this->assertGreaterThanOrEqual(10, $weeklyMenu->dailyMenus()->withCount('menuOptions')->get()->sum('menu_options_count'));
        $this->assertGreaterThan(0, MenuSelection::query()->count());

        $this->assertSame(
            0,
            MenuSelection::query()
                ->selectRaw('user_id, daily_menu_id, count(*) as aggregate')
                ->groupBy('user_id', 'daily_menu_id')
                ->having('aggregate', '>', 1)
                ->count(),
        );
    }
}
