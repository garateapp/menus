<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MenuSelection;
use App\Models\WeeklyMenu;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('SuperAdmin/Reports/Index', [
            'summary' => [
                'totalSelections' => MenuSelection::query()->count(),
                'weeklyMenus' => WeeklyMenu::query()->count(),
                'todaySelections' => MenuSelection::query()->whereDate('selected_at', today())->count(),
            ],
        ]);
    }
}
