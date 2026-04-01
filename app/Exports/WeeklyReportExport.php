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
            ['Fecha', 'Estado', 'Usuario', 'Correo', 'Username', 'Selección', 'Respuesta'],
        ];

        foreach ($this->report['summaryRows'] as $row) {
            $summaryRows[] = [
                $row['menuDate'],
                $row['status'],
                $row['userName'] ?: 'Sin nombre',
                $row['userEmail'] ?: 'Sin correo',
                $row['username'] ?: '-',
                $row['optionTitle'],
                $row['responseStatus'],
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
                ['No respondidos', $day['noResponsesCount']],
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

            if (count($dayRows) === 6) {
                $dayRows[] = [$day['menuDate'], 'Sin alternativas', '', 0];
            }

            $dayRows[] = [];
            $dayRows[] = ['Usuario sin respuesta', 'Correo', 'Username', 'Estado'];

            foreach ($day['noResponses'] as $user) {
                $dayRows[] = [
                    $user['userName'] ?: 'Sin nombre',
                    $user['userEmail'] ?: 'Sin correo',
                    $user['username'] ?: '-',
                    'Sin respuesta',
                ];
            }

            $sheets[] = new ArraySheetExport('Dia '.$day['menuDate'], $dayRows);
        }

        return $sheets;
    }
}
