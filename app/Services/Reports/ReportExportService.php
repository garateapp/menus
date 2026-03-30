<?php

namespace App\Services\Reports;

use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ReportExportService
{
    public function excelResponse(string $filename, string $title, array $sections): Response
    {
        $content = view('exports.report-excel', [
            'title' => $title,
            'sections' => $sections,
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$this->sanitizeFilename($filename).'.xls"',
        ]);
    }

    private function sanitizeFilename(string $filename): string
    {
        return Str::of($filename)
            ->ascii()
            ->replace(' ', '-')
            ->replaceMatches('/[^A-Za-z0-9\-_]/', '')
            ->trim('-')
            ->lower()
            ->value();
    }
}
