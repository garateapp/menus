<?php

namespace Tests\Feature\Worker;

use App\Enums\MenuStatus;
use App\Models\DailyMenu;
use App\Models\MenuOption;
use App\Models\MenuSelection;
use App\Models\User;
use App\Models\WeeklyMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_can_select_one_option_for_a_published_day(): void
    {
        $worker = $this->createWorker();
        [$dailyMenu, $menuOption] = $this->createPublishedMenuScenario();

        $response = $this->actingAs($worker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $menuOption->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('menu_selections', [
            'user_id' => $worker->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $menuOption->id,
        ]);
    }

    public function test_second_selection_updates_the_existing_record_for_the_same_day(): void
    {
        $worker = $this->createWorker();
        [$dailyMenu, $firstOption, $secondOption] = $this->createPublishedMenuScenario(withSecondOption: true);

        $this->actingAs($worker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $firstOption->id,
        ]);

        $response = $this->actingAs($worker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $secondOption->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('menu_selections', 1);
        $this->assertDatabaseHas('menu_selections', [
            'user_id' => $worker->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $secondOption->id,
        ]);
    }

    public function test_worker_can_select_when_the_day_is_published_even_if_the_week_is_still_draft(): void
    {
        $worker = $this->createWorker();
        [$dailyMenu, $menuOption] = $this->createPublishedMenuScenario(weekStatus: MenuStatus::Draft);

        $response = $this->actingAs($worker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $menuOption->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('menu_selections', [
            'user_id' => $worker->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $menuOption->id,
        ]);
    }

    public function test_worker_can_not_select_when_the_week_is_closed(): void
    {
        $worker = $this->createWorker();
        [$dailyMenu, $menuOption] = $this->createPublishedMenuScenario(weekStatus: MenuStatus::Closed);

        $response = $this->actingAs($worker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $menuOption->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('menu_selections', 0);
    }

    public function test_worker_can_not_select_when_the_day_is_closed(): void
    {
        $worker = $this->createWorker();
        [$dailyMenu, $menuOption] = $this->createPublishedMenuScenario(dayStatus: MenuStatus::Closed);

        $response = $this->actingAs($worker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $menuOption->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('menu_selections', 0);
    }

    public function test_worker_can_not_select_a_hidden_option(): void
    {
        $worker = $this->createWorker();
        [$dailyMenu, $menuOption] = $this->createPublishedMenuScenario(optionVisible: false);

        $response = $this->actingAs($worker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $menuOption->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('menu_selections', 0);
    }

    public function test_worker_can_not_select_an_option_when_its_quota_is_full(): void
    {
        $firstWorker = $this->createWorker();
        $secondWorker = $this->createWorker();
        [$dailyMenu, $menuOption] = $this->createPublishedMenuScenario(firstQuota: 1);

        $this->actingAs($firstWorker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $menuOption->id,
        ]);

        $response = $this->from(route('worker.menus.show', $dailyMenu))
            ->actingAs($secondWorker)
            ->post(route('worker.selections.store', $dailyMenu), [
                'menu_option_id' => $menuOption->id,
            ]);

        $response->assertRedirect(route('worker.menus.show', $dailyMenu));
        $response->assertSessionHas('error', 'No quedan cupos disponibles para esta alternativa.');
        $this->assertDatabaseCount('menu_selections', 1);
    }

    public function test_switching_selection_releases_the_previous_option_quota(): void
    {
        $firstWorker = $this->createWorker();
        $secondWorker = $this->createWorker();
        [$dailyMenu, $firstOption, $secondOption] = $this->createPublishedMenuScenario(withSecondOption: true, firstQuota: 1, secondQuota: 1);

        $this->actingAs($firstWorker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $firstOption->id,
        ]);

        $this->actingAs($firstWorker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $secondOption->id,
        ]);

        $response = $this->actingAs($secondWorker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $firstOption->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('menu_selections', 2);
        $this->assertDatabaseHas('menu_selections', [
            'user_id' => $firstWorker->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $secondOption->id,
        ]);
        $this->assertDatabaseHas('menu_selections', [
            'user_id' => $secondWorker->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $firstOption->id,
        ]);
    }

    public function test_worker_can_keep_the_same_selection_even_if_it_reaches_zero_remaining_quota(): void
    {
        $worker = $this->createWorker();
        [$dailyMenu, $menuOption] = $this->createPublishedMenuScenario(firstQuota: 1);

        $this->actingAs($worker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $menuOption->id,
        ]);

        $response = $this->actingAs($worker)->post(route('worker.selections.store', $dailyMenu), [
            'menu_option_id' => $menuOption->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('menu_selections', 1);
        $this->assertDatabaseHas('menu_selections', [
            'user_id' => $worker->id,
            'daily_menu_id' => $dailyMenu->id,
            'menu_option_id' => $menuOption->id,
        ]);
    }

    public function test_worker_calendar_shows_published_days_even_if_the_week_is_in_draft(): void
    {
        $worker = $this->createWorker();
        [$dailyMenu] = $this->createPublishedMenuScenario(weekStatus: MenuStatus::Draft);

        $response = $this->actingAs($worker)->get(route('worker.menus.index', [
            'view' => 'week',
            'date' => $dailyMenu->menu_date->toDateString(),
        ]));

        $response->assertInertia(fn ($page) => $page
            ->component('Worker/Menus/Index')
            ->where('calendar.cells.0.date', $dailyMenu->menu_date->toDateString())
            ->where('calendar.cells.0.has_day', true)
            ->where('calendar.cells.0.status', MenuStatus::Published->value)
            ->where('calendar.cells.0.options_count', 1)
        );
    }

    private function createWorker(): User
    {
        Role::findOrCreate('Worker', 'web');

        $worker = User::factory()->create();
        $worker->assignRole('Worker');

        return $worker;
    }

    private function createPublishedMenuScenario(
        MenuStatus $weekStatus = MenuStatus::Published,
        MenuStatus $dayStatus = MenuStatus::Published,
        bool $optionVisible = true,
        bool $withSecondOption = false,
        ?int $firstQuota = null,
        ?int $secondQuota = null,
    ): array {
        Role::findOrCreate('Supplier', 'web');

        $supplier = User::factory()->create();
        $supplier->assignRole('Supplier');

        $weeklyMenu = WeeklyMenu::create([
            'supplier_id' => $supplier->id,
            'title' => 'Semana actual',
            'week_start_date' => now()->startOfWeek()->toDateString(),
            'week_end_date' => now()->endOfWeek()->toDateString(),
            'status' => $weekStatus,
        ]);

        $dailyMenu = DailyMenu::create([
            'weekly_menu_id' => $weeklyMenu->id,
            'menu_date' => now()->toDateString(),
            'status' => $dayStatus,
        ]);

        $firstOption = MenuOption::create([
            'daily_menu_id' => $dailyMenu->id,
            'title' => 'Pollo al jugo',
            'description' => 'Con arroz',
            'quota' => $firstQuota,
            'is_visible' => $optionVisible,
            'sort_order' => 1,
        ]);

        if (! $withSecondOption) {
            return [$dailyMenu, $firstOption];
        }

        $secondOption = MenuOption::create([
            'daily_menu_id' => $dailyMenu->id,
            'title' => 'Pasta primavera',
            'description' => 'Con verduras',
            'quota' => $secondQuota,
            'is_visible' => true,
            'sort_order' => 2,
        ]);

        return [$dailyMenu, $firstOption, $secondOption];
    }
}
