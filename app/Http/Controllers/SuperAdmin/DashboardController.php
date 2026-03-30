<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\MenuSelection;
use App\Models\User;
use App\Models\WeeklyMenu;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => [
                'totalUsers' => User::query()->count(),
                'totalWorkers' => User::role('Worker')->count(),
                'totalSuppliers' => User::role('Supplier')->count(),
                'activeWeeks' => WeeklyMenu::query()->where('status', MenuStatus::Published)->count(),
                'todaySelections' => MenuSelection::query()->whereDate('selected_at', today())->count(),
            ],
        ]);
    }
}
