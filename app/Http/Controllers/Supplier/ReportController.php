<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Services\Reports\DailyReportService;
use App\Services\Reports\ReportExportService;
use App\Services\Reports\WeeklyReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function daily(Request $request, DailyReportService $dailyReportService): Response
    {
        $selectedDate = $request->query('date') ?: today()->toDateString();
        $availableDates = $dailyReportService->availableDatesForSupplier($request->user());

        return Inertia::render('Supplier/Reports/Daily', [
            'selectedDate' => $selectedDate,
            'availableDates' => $availableDates,
            'report' => $dailyReportService->buildForSupplier($request->user(), $selectedDate),
        ]);
    }

    public function weekly(Request $request, WeeklyReportService $weeklyReportService): Response
    {
        $weeklyMenuId = $request->query('weekly_menu_id');
        $selectedDay = $request->query('day');
        $availableWeeks = $weeklyReportService->availableWeeksForSupplier($request->user());

        return Inertia::render('Supplier/Reports/Weekly', [
            'availableWeeks' => $availableWeeks,
            'selectedWeeklyMenuId' => $weeklyMenuId ? (int) $weeklyMenuId : null,
            'selectedDay' => $selectedDay,
            'report' => $weeklyReportService->buildForSupplier($request->user(), $weeklyMenuId, $selectedDay),
        ]);
    }

    public function exportDaily(
        Request $request,
        DailyReportService $dailyReportService,
        ReportExportService $reportExportService,
    ): BinaryFileResponse {
        $selectedDate = $request->query('date') ?: today()->toDateString();
        $report = $dailyReportService->buildForSupplier($request->user(), $selectedDate);

        abort_unless($report, 404);

        return $reportExportService->downloadDailyReport($report);
    }

    public function exportWeekly(
        Request $request,
        WeeklyReportService $weeklyReportService,
        ReportExportService $reportExportService,
    ): BinaryFileResponse {
        $report = $weeklyReportService->buildForSupplier(
            $request->user(),
            $request->query('weekly_menu_id'),
            $request->query('day'),
        );

        abort_unless($report, 404);

        return $reportExportService->downloadWeeklyReport($report);
    }
}
