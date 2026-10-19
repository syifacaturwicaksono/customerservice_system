<?php

namespace App\Exports;

use App\Models\WaLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WaDashboardSummaryExport implements FromCollection, WithStyles, WithColumnWidths, WithTitle
{
    protected ?string $from;
    protected ?string $to;

    public function __construct(?string $from = null, ?string $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function title(): string
    {
        return 'Dashboard Summary';
    }

    public function collection(): Collection
    {
        $query = WaLog::query();

        if ($this->from && $this->to) {
            $query->whereBetween('created_at', [
                $this->from . ' 00:00:00',
                $this->to . ' 23:59:59',
            ]);
        }

        $totalWa = (clone $query)->count();
        $totalSent = (clone $query)->where('status', 'sent')->count();
        $totalFailed = (clone $query)->where('status', '!=', 'sent')->count();

        $period = 'Semua tanggal';
        if ($this->from && $this->to) {
            $period = date('d-m-Y', strtotime($this->from)) . ' s/d ' . date('d-m-Y', strtotime($this->to));
        }

        return collect([
            ['Informasi', 'Nilai'],
            ['Periode', $period],
            ['Total WA Terkirim', $totalWa],
            ['Status Sent', $totalSent],
            ['Status Failed', $totalFailed],
            ['Tanggal Export', now()->format('d-m-Y H:i:s')],
        ]);
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:B6')->getAlignment()->setVertical('center');
        $sheet->getStyle('A2:A6')->getFont()->setBold(true);
        $sheet->freezePane('A2');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
        ];
    }
}
