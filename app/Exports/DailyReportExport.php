<?php

namespace App\Exports;

use App\Exports\Sheets\ArraySheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DailyReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly array $report,
    ) {}

    public function sheets(): array
    {
        $summaryRows = [
            ['Reporte diario', $this->report['date']],
            ['Semana', $this->report['weekTitle']],
            ['Total a confeccionar', $this->report['totalSelections']],
            [],
            ['Fecha', 'Alternativa', 'Descripción', 'Cantidad seleccionada'],
        ];

        foreach ($this->report['options'] as $option) {
            $summaryRows[] = [
                $this->report['date'],
                $option['title'],
                $option['description'] ?: 'Sin descripción',
                $option['totalSelections'],
            ];
        }

        $deliveryRows = [
            ['Fecha', 'Usuario', 'Correo', 'Username', 'Menú elegido', 'Registrado'],
        ];

        foreach ($this->report['selections'] as $selection) {
            $deliveryRows[] = [
                $this->report['date'],
                $selection['userName'] ?: 'Sin nombre',
                $selection['userEmail'] ?: 'Sin correo',
                $selection['username'] ?: '-',
                $selection['optionTitle'] ?: 'Sin alternativa',
                $selection['selectedAt'] ?: '-',
            ];
        }

        $noResponseRows = [
            ['Fecha', 'Usuario', 'Correo', 'Username', 'Estado'],
        ];

        foreach ($this->report['noResponses'] ?? [] as $user) {
            $noResponseRows[] = [
                $this->report['date'],
                $user['userName'] ?: 'Sin nombre',
                $user['userEmail'] ?: 'Sin correo',
                $user['username'] ?: '-',
                'Sin respuesta',
            ];
        }

        return [
            new ArraySheetExport('Reporte diario', $summaryRows),
            new ArraySheetExport('Entregas', $deliveryRows),
            new ArraySheetExport('No respondidos', $noResponseRows),
        ];
    }
}
