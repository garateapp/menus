<?php

namespace Tests\Feature\Reports;

use App\Enums\MenuStatus;
use App\Models\DailyMenu;
use App\Models\MenuOption;
use App\Models\MenuSelection;
use App\Models\User;
use App\Models\WeeklyMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_can_view_a_daily_report_for_their_published_menu(): void
    {
        $supplier = $this->createSupplier();
        [$weeklyMenu, $dailyMenu, $firstOption, $secondOption] = $this->createReportScenario($supplier);

        $firstWorker = $this->createWorker(['name' => 'Juan Pérez']);
        $secondWorker = $this->createWorker(['name' => 'María Soto']);

        MenuSelection::query()->create([
            'user_id' => $firstWorker->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $firstOption->id,
            'selected_at' => now(),
        ]);

        MenuSelection::query()->create([
            'user_id' => $secondWorker->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $secondOption->id,
            'selected_at' => now(),
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.reports.daily', [
            'date' => $dailyMenu->menu_date->toDateString(),
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/Reports/Daily')
            ->where('report.totalSelections', 2)
            ->has('report.options', 2)
            ->has('report.selections', 2)
            ->where('report.selections.0.userName', 'Juan Pérez')
            ->where('report.selections.0.optionTitle', 'Pollo')
        );
    }

    public function test_supplier_can_view_a_weekly_report(): void
    {
        $supplier = $this->createSupplier();
        [$weeklyMenu, $dailyMenu, $firstOption] = $this->createReportScenario($supplier);

        MenuSelection::query()->create([
            'user_id' => $this->createWorker()->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $firstOption->id,
            'selected_at' => now(),
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.reports.weekly', [
            'weekly_menu_id' => $weeklyMenu->id,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/Reports/Weekly')
            ->where('report.totalSelections', 1)
            ->has('report.days', 1)
        );
    }

    public function test_daily_report_defaults_to_today_when_the_supplier_has_data_for_today(): void
    {
        $supplier = $this->createSupplier();
        [$weeklyMenu, $dailyMenu] = $this->createReportScenario($supplier);

        DailyMenu::query()->create([
            'weekly_menu_id' => $weeklyMenu->id,
            'menu_date' => now()->addDay()->toDateString(),
            'status' => MenuStatus::Published,
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.reports.daily'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/Reports/Daily')
            ->where('selectedDate', today()->toDateString())
            ->where('report.date', $dailyMenu->menu_date->toDateString())
        );
    }

    public function test_daily_report_defaults_to_today_even_without_menu_data(): void
    {
        $supplier = $this->createSupplier();

        $response = $this->actingAs($supplier)->get(route('supplier.reports.daily'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/Reports/Daily')
            ->where('selectedDate', today()->toDateString())
        );
    }

    public function test_weekly_report_defaults_to_the_latest_available_week_for_the_supplier(): void
    {
        $supplier = $this->createSupplier();
        [$firstWeeklyMenu] = $this->createReportScenario($supplier);

        $laterWeeklyMenu = WeeklyMenu::query()->create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana reciente',
            'week_start_date' => now()->startOfWeek()->addWeek()->toDateString(),
            'week_end_date' => now()->endOfWeek()->addWeek()->toDateString(),
            'status' => MenuStatus::Published,
            'notes' => null,
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.reports.weekly'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/Reports/Weekly')
            ->where('report.weeklyMenuId', $laterWeeklyMenu->id)
            ->where('report.title', $laterWeeklyMenu->title)
        );

        $this->assertNotSame($firstWeeklyMenu->id, $laterWeeklyMenu->id);
    }

    public function test_weekly_report_supports_selecting_a_day_inside_the_week(): void
    {
        $supplier = $this->createSupplier();
        [$weeklyMenu, $dailyMenu, $firstOption, $secondOption] = $this->createReportScenario($supplier);

        $secondDay = DailyMenu::query()->create([
            'weekly_menu_id' => $weeklyMenu->id,
            'menu_date' => now()->addDay()->toDateString(),
            'status' => MenuStatus::Published,
        ]);

        $thirdOption = MenuOption::query()->create([
            'daily_menu_id' => $secondDay->id,
            'title' => 'Vegetariano',
            'description' => null,
            'quota' => null,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        MenuSelection::query()->create([
            'user_id' => $this->createWorker()->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $firstOption->id,
            'selected_at' => now(),
        ]);

        MenuSelection::query()->create([
            'user_id' => $this->createWorker()->id,
            'daily_menu_id' => $secondDay->id,
            'menu_option_id' => $thirdOption->id,
            'selected_at' => now(),
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.reports.weekly', [
            'weekly_menu_id' => $weeklyMenu->id,
            'day' => $secondDay->menu_date->toDateString(),
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/Reports/Weekly')
            ->where('selectedDay', $secondDay->menu_date->toDateString())
            ->where('report.selectedDay.date', $secondDay->menu_date->toDateString())
            ->where('report.selectedDay.totalSelections', 1)
            ->has('report.selectedDay.options', 1)
        );
    }

    public function test_supplier_can_export_the_weekly_report_as_excel_compatible_file(): void
    {
        $supplier = $this->createSupplier();
        [$weeklyMenu, $dailyMenu, $firstOption] = $this->createReportScenario($supplier);

        MenuSelection::query()->create([
            'user_id' => $this->createWorker()->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $firstOption->id,
            'selected_at' => now(),
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.reports.weekly.export', [
            'weekly_menu_id' => $weeklyMenu->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertDownload('reporte-semanal-'.$weeklyMenu->id.'.xlsx');
    }

    public function test_supplier_can_export_the_daily_report_as_native_excel_file(): void
    {
        $supplier = $this->createSupplier();
        [$weeklyMenu, $dailyMenu, $firstOption] = $this->createReportScenario($supplier);

        MenuSelection::query()->create([
            'user_id' => $this->createWorker()->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $firstOption->id,
            'selected_at' => now(),
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.reports.daily.export', [
            'date' => $dailyMenu->menu_date->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertDownload('reporte-diario-'.$dailyMenu->menu_date->toDateString().'.xlsx');
    }

    public function test_supplier_report_pages_render_empty_state_instead_of_404_when_no_data_exists(): void
    {
        $supplier = $this->createSupplier();

        $dailyResponse = $this->actingAs($supplier)->get(route('supplier.reports.daily'));
        $weeklyResponse = $this->actingAs($supplier)->get(route('supplier.reports.weekly'));

        $dailyResponse->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/Reports/Daily')
            ->where('report', null)
        );

        $weeklyResponse->assertInertia(fn (Assert $page) => $page
            ->component('Supplier/Reports/Weekly')
            ->where('report', null)
        );
    }

    public function test_superadmin_can_view_the_global_reports_index(): void
    {
        $supplier = $this->createSupplier();
        [$weeklyMenu, $dailyMenu, $firstOption] = $this->createReportScenario($supplier);
        $worker = $this->createWorker(['name' => 'Trabajador Control']);

        MenuSelection::query()->create([
            'user_id' => $worker->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $firstOption->id,
            'selected_at' => now(),
        ]);

        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('superadmin.reports.index', [
            'date' => $dailyMenu->menu_date->toDateString(),
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('SuperAdmin/Reports/Index')
            ->where('selectedDate', $dailyMenu->menu_date->toDateString())
            ->where('dailyReport.totalSelections', 1)
            ->has('dailyReport.selections', 1)
            ->where('dailyReport.selections.0.userName', 'Trabajador Control')
            ->where('dailyReport.selections.0.optionTitle', 'Pollo')
        );
    }

    private function createSupplier(): User
    {
        Role::findOrCreate('Supplier', 'web');

        $supplier = User::factory()->create();
        $supplier->assignRole('Supplier');

        return $supplier;
    }

    private function createWorker(array $attributes = []): User
    {
        Role::findOrCreate('Worker', 'web');

        $worker = User::factory()->create($attributes);
        $worker->assignRole('Worker');

        return $worker;
    }

    private function createSuperAdmin(array $attributes = []): User
    {
        Role::findOrCreate('SuperAdmin', 'web');

        $superAdmin = User::factory()->create($attributes);
        $superAdmin->assignRole('SuperAdmin');

        return $superAdmin;
    }

    private function createReportScenario(User $supplier): array
    {
        $weeklyMenu = WeeklyMenu::query()->create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana reporte',
            'week_start_date' => now()->startOfWeek()->toDateString(),
            'week_end_date' => now()->endOfWeek()->toDateString(),
            'status' => MenuStatus::Published,
            'notes' => null,
        ]);

        $dailyMenu = DailyMenu::query()->create([
            'weekly_menu_id' => $weeklyMenu->id,
            'menu_date' => now()->toDateString(),
            'status' => MenuStatus::Published,
        ]);

        $firstOption = MenuOption::query()->create([
            'daily_menu_id' => $dailyMenu->id,
            'title' => 'Pollo',
            'description' => null,
            'quota' => null,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $secondOption = MenuOption::query()->create([
            'daily_menu_id' => $dailyMenu->id,
            'title' => 'Pasta',
            'description' => null,
            'quota' => null,
            'is_visible' => true,
            'sort_order' => 2,
        ]);

        return [$weeklyMenu, $dailyMenu, $firstOption, $secondOption];
    }
}
