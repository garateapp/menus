<?php

namespace Tests\Feature\Supplier;

use App\Enums\MenuStatus;
use App\Models\DailyMenu;
use App\Models\MenuOption;
use App\Models\User;
use App\Models\WeeklyMenu;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WeeklyMenuManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_can_create_a_weekly_menu(): void
    {
        $supplier = $this->createSupplier();

        $response = $this->actingAs($supplier)->post(route('supplier.weekly-menus.store'), [
            'title' => 'Semana 14',
            'week_start_date' => now()->startOfWeek()->toDateString(),
            'week_end_date' => now()->endOfWeek()->toDateString(),
            'status' => MenuStatus::Draft->value,
            'notes' => 'Menu inicial',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('weekly_menus', [
            'supplier_id' => $supplier->id,
            'title' => 'Semana 14',
            'status' => MenuStatus::Draft->value,
        ]);
    }

    public function test_supplier_can_create_a_daily_menu_inside_their_week(): void
    {
        $supplier = $this->createSupplier();
        $weeklyMenu = $this->createWeeklyMenu($supplier);

        $response = $this->actingAs($supplier)->post(route('supplier.daily-menus.store', $weeklyMenu), [
            'menu_date' => now()->addDay()->toDateString(),
            'status' => MenuStatus::Published->value,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('daily_menus', [
            'weekly_menu_id' => $weeklyMenu->id,
            'status' => MenuStatus::Published->value,
        ]);
    }

    public function test_supplier_can_update_their_weekly_menu_and_change_status(): void
    {
        $supplier = $this->createSupplier();
        $weeklyMenu = $this->createWeeklyMenu($supplier);

        $response = $this->actingAs($supplier)->patch(route('supplier.weekly-menus.update', $weeklyMenu), [
            'title' => 'Semana actualizada',
            'week_start_date' => now()->startOfWeek()->addWeek()->toDateString(),
            'week_end_date' => now()->endOfWeek()->addWeek()->toDateString(),
            'status' => MenuStatus::Published->value,
            'notes' => 'Notas actualizadas',
        ]);

        $response->assertRedirect(route('supplier.weekly-menus.show', $weeklyMenu));
        $this->assertDatabaseHas('weekly_menus', [
            'id' => $weeklyMenu->id,
            'title' => 'Semana actualizada',
            'status' => MenuStatus::Published->value,
            'notes' => 'Notas actualizadas',
        ]);
    }

    public function test_supplier_can_update_a_daily_menu_and_change_status(): void
    {
        $supplier = $this->createSupplier();
        $dailyMenu = $this->createDailyMenu($supplier);

        $response = $this->actingAs($supplier)->patch(route('supplier.daily-menus.update', $dailyMenu), [
            'menu_date' => now()->addDays(2)->toDateString(),
            'status' => MenuStatus::Published->value,
        ]);

        $response->assertRedirect(route('supplier.weekly-menus.show', $dailyMenu->weeklyMenu));
        $dailyMenu->refresh();

        $this->assertSame(now()->addDays(2)->toDateString(), $dailyMenu->menu_date->toDateString());
        $this->assertSame(MenuStatus::Published, $dailyMenu->status);
    }

    public function test_supplier_can_not_create_the_same_daily_menu_date_twice_in_one_week(): void
    {
        $supplier = $this->createSupplier();
        $weeklyMenu = $this->createWeeklyMenu($supplier);
        $menuDate = now()->addDay()->toDateString();

        DailyMenu::create([
            'weekly_menu_id' => $weeklyMenu->id,
            'menu_date' => $menuDate,
            'status' => MenuStatus::Draft,
        ]);

        $response = $this->from(route('supplier.weekly-menus.show', $weeklyMenu))
            ->actingAs($supplier)
            ->post(route('supplier.daily-menus.store', $weeklyMenu), [
                'menu_date' => $menuDate,
                'status' => MenuStatus::Published->value,
            ]);

        $response->assertRedirect(route('supplier.weekly-menus.show', $weeklyMenu));
        $response->assertSessionHasErrors('menu_date');
        $this->assertDatabaseCount('daily_menus', 1);
    }

    public function test_supplier_calendar_marks_existing_daily_menu_as_configured(): void
    {
        Carbon::setTestNow('2026-03-30 09:00:00');

        $supplier = $this->createSupplier();
        $weeklyMenu = WeeklyMenu::query()->create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana N° 14',
            'week_start_date' => '2026-03-30',
            'week_end_date' => '2026-04-05',
            'status' => MenuStatus::Draft,
            'notes' => null,
        ]);

        $dailyMenu = DailyMenu::query()->create([
            'weekly_menu_id' => $weeklyMenu->id,
            'menu_date' => '2026-04-01',
            'status' => MenuStatus::Draft,
        ]);

        MenuOption::query()->create([
            'daily_menu_id' => $dailyMenu->id,
            'title' => 'Pasta',
            'description' => null,
            'quota' => null,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.weekly-menus.index', [
            'view' => 'week',
            'date' => '2026-03-30',
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/WeeklyMenus/Index')
            ->where('calendar.cells.2.date', '2026-04-01')
            ->where('calendar.cells.2.has_week', true)
            ->where('calendar.cells.2.has_day', true)
            ->where('calendar.cells.2.options_count', 1)
        );
    }

    public function test_supplier_calendar_auto_creates_current_and_fifty_two_future_weeks_as_draft(): void
    {
        Carbon::setTestNow('2026-03-30 09:00:00');

        $supplier = $this->createSupplier();

        $response = $this->actingAs($supplier)->get(route('supplier.weekly-menus.index', [
            'view' => 'week',
            'date' => '2026-03-30',
        ]));

        $response->assertOk();

        $this->assertSame(53, WeeklyMenu::query()->where('supplier_id', $supplier->id)->count());

        $currentWeek = WeeklyMenu::query()
            ->where('supplier_id', $supplier->id)
            ->whereDate('week_start_date', '2026-03-30')
            ->first();
        $expectedLastStart = CarbonImmutable::parse('2026-03-30')->startOfWeek()->addWeeks(52);
        $lastWeek = WeeklyMenu::query()
            ->where('supplier_id', $supplier->id)
            ->whereDate('week_start_date', $expectedLastStart->toDateString())
            ->first();

        $this->assertNotNull($currentWeek);
        $this->assertSame('Semana N° 14', $currentWeek->title);
        $this->assertSame(MenuStatus::Draft, $currentWeek->status);
        $this->assertSame('2026-03-30', $currentWeek->week_start_date->toDateString());
        $this->assertSame('2026-04-05', $currentWeek->week_end_date->toDateString());

        $this->assertNotNull($lastWeek);
        $this->assertSame(sprintf('Semana N° %s', $expectedLastStart->format('W')), $lastWeek->title);
        $this->assertSame(MenuStatus::Draft, $lastWeek->status);
        $this->assertSame($expectedLastStart->toDateString(), $lastWeek->week_start_date->toDateString());
        $this->assertSame($expectedLastStart->endOfWeek()->toDateString(), $lastWeek->week_end_date->toDateString());
    }

    public function test_supplier_calendar_prioritizes_the_exact_daily_menu_when_weeks_overlap(): void
    {
        Carbon::setTestNow('2026-03-30 09:00:00');

        $supplier = $this->createSupplier();

        $canonicalWeek = WeeklyMenu::query()->create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana N° 14',
            'week_start_date' => '2026-03-30',
            'week_end_date' => '2026-04-05',
            'status' => MenuStatus::Draft,
            'notes' => null,
        ]);

        $overlappingWeek = WeeklyMenu::query()->create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana 14',
            'week_start_date' => '2026-04-01',
            'week_end_date' => '2026-04-03',
            'status' => MenuStatus::Published,
            'notes' => null,
        ]);

        $dailyMenu = DailyMenu::query()->create([
            'weekly_menu_id' => $overlappingWeek->id,
            'menu_date' => '2026-04-01',
            'status' => MenuStatus::Published,
        ]);

        MenuOption::query()->create([
            'daily_menu_id' => $dailyMenu->id,
            'title' => 'Pescado',
            'description' => null,
            'quota' => null,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.weekly-menus.index', [
            'view' => 'week',
            'date' => '2026-03-30',
            'selected_date' => '2026-04-01',
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/WeeklyMenus/Index')
            ->where('calendar.cells.2.date', '2026-04-01')
            ->where('calendar.cells.2.has_day', true)
            ->where('calendar.cells.2.weekly_menu_id', $overlappingWeek->id)
            ->where('calendar.cells.2.title', 'Semana 14')
            ->where('selectedWeeklyMenu.id', $overlappingWeek->id)
            ->where('selectedDailyMenu.id', $dailyMenu->id)
        );

        $this->assertNotSame($canonicalWeek->id, $overlappingWeek->id);
    }

    public function test_supplier_calendar_serializes_selected_week_dates_as_plain_dates(): void
    {
        Carbon::setTestNow('2026-03-30 09:00:00');

        $supplier = $this->createSupplier();

        $weeklyMenu = WeeklyMenu::query()->create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana N° 14',
            'week_start_date' => '2026-03-30',
            'week_end_date' => '2026-04-05',
            'status' => MenuStatus::Draft,
            'notes' => null,
        ]);

        $dailyMenu = DailyMenu::query()->create([
            'weekly_menu_id' => $weeklyMenu->id,
            'menu_date' => '2026-03-30',
            'status' => MenuStatus::Draft,
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.weekly-menus.index', [
            'view' => 'week',
            'date' => '2026-03-30',
            'selected_date' => '2026-03-30',
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/WeeklyMenus/Index')
            ->where('selectedWeekDefaults.week_start_date', '2026-03-30')
            ->where('selectedWeekDefaults.week_end_date', '2026-04-05')
            ->where('selectedWeeklyMenu.week_start_date', '2026-03-30')
            ->where('selectedWeeklyMenu.week_end_date', '2026-04-05')
            ->where('selectedDailyMenu.menu_date', '2026-03-30')
            ->where('selectedDailyMenu.id', $dailyMenu->id)
        );
    }

    public function test_supplier_can_create_menu_options_inside_their_day(): void
    {
        $supplier = $this->createSupplier();
        $dailyMenu = $this->createDailyMenu($supplier);

        $response = $this->actingAs($supplier)->post(route('supplier.menu-options.store', $dailyMenu), [
            'title' => 'Pescado con pure',
            'description' => 'Opcion liviana',
            'quota' => null,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('menu_options', [
            'daily_menu_id' => $dailyMenu->id,
            'title' => 'Pescado con pure',
        ]);
        $this->assertDatabaseHas('menu_options', [
            'daily_menu_id' => $dailyMenu->id,
            'title' => 'No solicitaré menú',
            'is_opt_out' => true,
            'is_visible' => true,
        ]);
    }

    public function test_supplier_can_update_menu_options_inside_their_day(): void
    {
        $supplier = $this->createSupplier();
        $dailyMenu = $this->createDailyMenu($supplier);
        $option = MenuOption::factory()->create([
            'daily_menu_id' => $dailyMenu->id,
            'title' => 'Plato original',
            'is_visible' => true,
        ]);

        $response = $this->actingAs($supplier)->patch(route('supplier.menu-options.update', $option), [
            'title' => 'Plato actualizado',
            'description' => 'Nueva descripcion',
            'quota' => 25,
            'is_visible' => false,
            'sort_order' => 2,
        ]);

        $response->assertRedirect(route('supplier.weekly-menus.show', $dailyMenu->weeklyMenu));
        $this->assertDatabaseHas('menu_options', [
            'id' => $option->id,
            'title' => 'Plato actualizado',
            'description' => 'Nueva descripcion',
            'quota' => 25,
            'is_visible' => false,
            'sort_order' => 2,
        ]);
    }

    public function test_supplier_dashboard_prefers_the_current_week_over_future_draft_weeks(): void
    {
        Carbon::setTestNow('2026-03-30 09:00:00');

        $supplier = $this->createSupplier();

        WeeklyMenu::query()->create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana N° 14',
            'week_start_date' => '2026-03-30',
            'week_end_date' => '2026-04-05',
            'status' => MenuStatus::Published,
            'notes' => null,
        ]);

        WeeklyMenu::query()->create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana N° 13',
            'week_start_date' => '2027-03-29',
            'week_end_date' => '2027-04-04',
            'status' => MenuStatus::Draft,
            'notes' => null,
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/Dashboard')
            ->where('activeWeek.title', 'Semana N° 14')
            ->where('activeWeek.week_start_date', '2026-03-30')
            ->where('activeWeek.week_end_date', '2026-04-05')
        );
    }

    public function test_supplier_can_not_manage_another_suppliers_weekly_menu(): void
    {
        $owner = $this->createSupplier();
        $otherSupplier = $this->createSupplier();
        $weeklyMenu = $this->createWeeklyMenu($owner);

        $response = $this->actingAs($otherSupplier)->get(route('supplier.weekly-menus.edit', $weeklyMenu));

        $response->assertForbidden();
    }

    public function test_superadmin_can_view_the_global_menus_index(): void
    {
        Role::findOrCreate('SuperAdmin', 'web');

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('SuperAdmin');

        $response = $this->actingAs($superAdmin)->get(route('superadmin.menus.index'));

        $response->assertOk();
    }

    private function createSupplier(): User
    {
        Role::findOrCreate('Supplier', 'web');

        $supplier = User::factory()->create();
        $supplier->assignRole('Supplier');

        return $supplier;
    }

    private function createWeeklyMenu(User $supplier): WeeklyMenu
    {
        return WeeklyMenu::create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana principal',
            'week_start_date' => now()->startOfWeek()->toDateString(),
            'week_end_date' => now()->endOfWeek()->toDateString(),
            'status' => MenuStatus::Draft,
            'notes' => 'Notas',
        ]);
    }

    private function createDailyMenu(User $supplier): DailyMenu
    {
        $weeklyMenu = $this->createWeeklyMenu($supplier);

        return DailyMenu::create([
            'weekly_menu_id' => $weeklyMenu->id,
            'menu_date' => now()->toDateString(),
            'status' => MenuStatus::Draft,
        ]);
    }
}
