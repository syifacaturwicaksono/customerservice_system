<?php

namespace App\Exports;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardSummaryExport implements FromCollection, WithStyles, WithColumnWidths, WithTitle
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
        $query = EmailLog::query();

        if ($this->from && $this->to) {
            $query->whereBetween('created_at', [
                $this->from . ' 00:00:00',
                $this->to . ' 23:59:59',
            ]);
        }

        $totalEmails = $query->count();
        $totalTemplates = EmailTemplate::count();

        $period = 'Semua tanggal';
        if ($this->from && $this->to) {
            $period = date('d-m-Y', strtotime($this->from)) . ' s/d ' . date('d-m-Y', strtotime($this->to));
        }

        return collect([
            ['Informasi', 'Nilai'],
            ['Periode', $period],
            ['Total Email Terkirim', $totalEmails],
            ['Total Jenis Pesan', $totalTemplates],
            ['Tanggal Export', now()->format('d-m-Y H:i:s')],
        ]);
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:B5')->getAlignment()->setVertical('center');

        $sheet->getStyle('A2:A5')->getFont()->setBold(true);

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
