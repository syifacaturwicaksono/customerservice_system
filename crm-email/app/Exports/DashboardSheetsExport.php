<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DashboardSheetsExport implements WithMultipleSheets
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
            new DashboardSummaryExport($this->from, $this->to),
            new DashboardExport($this->from, $this->to),
        ];
    }
}
