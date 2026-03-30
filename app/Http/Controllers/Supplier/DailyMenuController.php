<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreDailyMenuRequest;
use App\Models\DailyMenu;
use App\Models\WeeklyMenu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DailyMenuController extends Controller
{
    public function store(StoreDailyMenuRequest $request, WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->authorize('update', $weeklyMenu);

        $weeklyMenu->dailyMenus()->create($request->validated());

        if ($redirectResponse = $this->redirectToCalendarIfRequested($request, 'Día agregado correctamente.')) {
            return $redirectResponse;
        }

        return back()->with('success', 'Día agregado correctamente.');
    }

    public function update(StoreDailyMenuRequest $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $this->authorize('update', $dailyMenu->weeklyMenu);

        $dailyMenu->update($request->validated());

        if ($redirectResponse = $this->redirectToCalendarIfRequested($request, 'Día actualizado correctamente.')) {
            return $redirectResponse;
        }

        return redirect()
            ->route('supplier.weekly-menus.show', $dailyMenu->weeklyMenu)
            ->with('success', 'Día actualizado correctamente.');
    }

    private function redirectToCalendarIfRequested(Request $request, string $message): ?RedirectResponse
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo === '') {
            return null;
        }

        return redirect()->to($redirectTo)->with('success', $message);
    }
}
