<?php

namespace App\Exports;

use App\Exports\Sheets\ArraySheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WeeklyReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly array $report,
    ) {}

    public function sheets(): array
    {
        $summaryRows = [
            ['Reporte semanal', $this->report['title']],
            ['Estado', $this->report['status']],
            ['Total semanal', $this->report['totalSelections']],
            [],
            ['Fecha', 'Estado', 'Cantidad seleccionada'],
        ];

        foreach ($this->report['days'] as $day) {
            $summaryRows[] = [
                $day['menuDate'],
                $day['status'],
                $day['totalSelections'],
            ];
        }

        $sheets = [
            new ArraySheetExport('Resumen semanal', $summaryRows),
        ];

        foreach ($this->report['days'] as $day) {
            $dayRows = [
                ['Fecha', $day['menuDate']],
                ['Estado', $day['status']],
                ['Total a confeccionar', $day['totalSelections']],
                [],
                ['Fecha', 'Alternativa', 'Descripción', 'Cantidad seleccionada'],
            ];

            foreach ($day['options'] as $option) {
                $dayRows[] = [
                    $day['menuDate'],
                    $option['title'],
                    $option['description'] ?: 'Sin descripción',
                    $option['totalSelections'],
                ];
            }

            if (count($dayRows) === 5) {
                $dayRows[] = [$day['menuDate'], 'Sin alternativas', '', 0];
            }

            $sheets[] = new ArraySheetExport('Dia '.$day['menuDate'], $dayRows);
        }

        return $sheets;
    }
}
