<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WaDashboardSheetsExport implements WithMultipleSheets
{
    protected ?string $from;
    protected ?string $to;

    public function __construct(?string $from = null, ?string $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function sheets(): array
    {
        return [
            new WaDashboardSummaryExport($this->from, $this->to),
            new WaDashboardExport($this->from, $this->to),
        ];
    }
}
