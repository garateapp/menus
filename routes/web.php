<?php

use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\MenuController as SuperAdminMenuController;
use App\Http\Controllers\SuperAdmin\ReportController as SuperAdminReportController;
use App\Http\Controllers\Supplier\DashboardController as SupplierDashboardController;
use App\Http\Controllers\Supplier\DailyMenuController as SupplierDailyMenuController;
use App\Http\Controllers\Supplier\MenuOptionController as SupplierMenuOptionController;
use App\Http\Controllers\Supplier\ReportController as SupplierReportController;
use App\Http\Controllers\Supplier\WeeklyMenuController as SupplierWeeklyMenuController;
use App\Http\Controllers\Worker\DashboardController as WorkerDashboardController;
use App\Http\Controllers\Worker\MenuController as WorkerMenuController;
use App\Http\Controllers\Worker\SelectionController as WorkerSelectionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => false,
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $user = request()->user();

    if ($user->hasRole('SuperAdmin')) {
        return redirect()->route('superadmin.dashboard');
    }

    if ($user->hasRole('Supplier')) {
        return redirect()->route('supplier.dashboard');
    }

    return redirect()->route('worker.dashboard');
})->middleware(['auth', 'active'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'active', 'role:Worker'])->prefix('worker')->name('worker.')->group(function () {
    Route::get('/dashboard', WorkerDashboardController::class)->name('dashboard');
    Route::get('/menus', [WorkerMenuController::class, 'index'])->name('menus.index');
    Route::get('/menus/{dailyMenu}', [WorkerMenuController::class, 'show'])->name('menus.show');
    Route::get('/selections', [WorkerSelectionController::class, 'index'])->name('selections.index');
    Route::post('/daily-menus/{dailyMenu}/selections', [WorkerSelectionController::class, 'store'])->name('selections.store');
});

Route::middleware(['auth', 'active', 'role:Supplier'])->prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/dashboard', SupplierDashboardController::class)->name('dashboard');
    Route::get('/weekly-menus', [SupplierWeeklyMenuController::class, 'index'])->name('weekly-menus.index');
    Route::get('/weekly-menus/create', [SupplierWeeklyMenuController::class, 'create'])->name('weekly-menus.create');
    Route::post('/weekly-menus', [SupplierWeeklyMenuController::class, 'store'])->name('weekly-menus.store');
    Route::get('/weekly-menus/{weeklyMenu}', [SupplierWeeklyMenuController::class, 'show'])->name('weekly-menus.show');
    Route::get('/weekly-menus/{weeklyMenu}/edit', [SupplierWeeklyMenuController::class, 'edit'])->name('weekly-menus.edit');
    Route::patch('/weekly-menus/{weeklyMenu}', [SupplierWeeklyMenuController::class, 'update'])->name('weekly-menus.update');
    Route::post('/weekly-menus/{weeklyMenu}/daily-menus', [SupplierDailyMenuController::class, 'store'])->name('daily-menus.store');
    Route::patch('/daily-menus/{dailyMenu}', [SupplierDailyMenuController::class, 'update'])->name('daily-menus.update');
    Route::post('/daily-menus/{dailyMenu}/menu-options', [SupplierMenuOptionController::class, 'store'])->name('menu-options.store');
    Route::patch('/menu-options/{menuOption}', [SupplierMenuOptionController::class, 'update'])->name('menu-options.update');
    Route::get('/reports/daily', [SupplierReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/daily/export', [SupplierReportController::class, 'exportDaily'])->name('reports.daily.export');
    Route::get('/reports/weekly', [SupplierReportController::class, 'weekly'])->name('reports.weekly');
    Route::get('/reports/weekly/export', [SupplierReportController::class, 'exportWeekly'])->name('reports.weekly.export');
});

Route::middleware(['auth', 'active', 'role:SuperAdmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', SuperAdminDashboardController::class)->name('dashboard');
    Route::get('/users', [SuperAdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [SuperAdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [SuperAdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [SuperAdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [SuperAdminUserController::class, 'update'])->name('users.update');
    Route::get('/menus', [SuperAdminMenuController::class, 'index'])->name('menus.index');
    Route::get('/reports', [SuperAdminReportController::class, 'index'])->name('reports.index');
});

require __DIR__.'/auth.php';
