<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreMenuOptionRequest;
use App\Models\DailyMenu;
use App\Models\MenuOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuOptionController extends Controller
{
    public function store(StoreMenuOptionRequest $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $this->authorize('update', $dailyMenu->weeklyMenu);
        $imagePath = $request->file('image')?->store('menu-options', 'public');

        $dailyMenu->menuOptions()->create([
            'title' => $request->string('title')->toString(),
            'description' => $request->input('description'),
            'image_path' => $imagePath,
            'quota' => $request->input('quota'),
            'is_visible' => $request->boolean('is_visible'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        if ($redirectResponse = $this->redirectToCalendarIfRequested($request, 'Alternativa agregada correctamente.')) {
            return $redirectResponse;
        }

        return back()->with('success', 'Alternativa agregada correctamente.');
    }

    public function update(StoreMenuOptionRequest $request, MenuOption $menuOption): RedirectResponse
    {
        $this->authorize('update', $menuOption->dailyMenu->weeklyMenu);

        $imagePath = $menuOption->image_path;

        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('image')->store('menu-options', 'public');
        }

        $menuOption->update([
            'title' => $request->string('title')->toString(),
            'description' => $request->input('description'),
            'image_path' => $imagePath,
            'quota' => $request->input('quota'),
            'is_visible' => $request->boolean('is_visible'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        if ($redirectResponse = $this->redirectToCalendarIfRequested($request, 'Alternativa actualizada correctamente.')) {
            return $redirectResponse;
        }

        return redirect()
            ->route('supplier.weekly-menus.show', $menuOption->dailyMenu->weeklyMenu)
            ->with('success', 'Alternativa actualizada correctamente.');
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
