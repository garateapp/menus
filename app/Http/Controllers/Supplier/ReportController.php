<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Services\Reports\DailyReportService;
use App\Services\Reports\WeeklyReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function daily(Request $request, DailyReportService $dailyReportService): Response
    {
        $date = $request->query('date');

        return Inertia::render('Supplier/Reports/Daily', [
            'report' => $dailyReportService->buildForSupplier($request->user(), $date),
        ]);
    }

    public function weekly(Request $request, WeeklyReportService $weeklyReportService): Response
    {
        $weeklyMenuId = $request->query('weekly_menu_id');

        return Inertia::render('Supplier/Reports/Weekly', [
            'report' => $weeklyReportService->buildForSupplier($request->user(), $weeklyMenuId),
        ]);
    }
}
