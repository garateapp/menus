<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\WeeklyMenu;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('SuperAdmin/Menus/Index', [
            'weeklyMenus' => WeeklyMenu::query()
                ->with(['supplier', 'dailyMenus'])
                ->latest('week_start_date')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }
}
