<?php

namespace App\Services\Reports;

use App\Exports\DailyReportExport;
use App\Exports\WeeklyReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportService
{
    public function downloadDailyReport(array $report): BinaryFileResponse
    {
        return Excel::download(
            new DailyReportExport($report),
            'reporte-diario-'.$report['date'].'.xlsx'
        );
    }

    public function downloadWeeklyReport(array $report): BinaryFileResponse
    {
        return Excel::download(
            new WeeklyReportExport($report),
            'reporte-semanal-'.$report['weeklyMenuId'].'.xlsx'
        );
    }
}
