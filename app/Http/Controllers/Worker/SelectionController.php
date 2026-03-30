<?php

namespace App\Http\Controllers\Worker;

use App\Exceptions\InsufficientQuotaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\StoreMenuSelectionRequest;
use App\Models\DailyMenu;
use App\Models\MenuOption;
use App\Services\Menus\MenuSelectionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SelectionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Worker/Selections/Index', [
            'selections' => request()->user()
                ->menuSelections()
                ->with(['dailyMenu.weeklyMenu', 'menuOption'])
                ->latest('selected_at')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function store(
        StoreMenuSelectionRequest $request,
        DailyMenu $dailyMenu,
        MenuSelectionService $menuSelectionService,
    ): RedirectResponse {
        $menuOption = MenuOption::query()->findOrFail($request->integer('menu_option_id'));

        try {
            $menuSelectionService->select($request->user(), $dailyMenu, $menuOption);
        } catch (InsufficientQuotaException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Selección guardada correctamente.');
    }
}
