<?php

namespace App\Exports;

use App\Models\WaLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WaDashboardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
        return 'Detail WhatsApp';
    }

    public function collection(): Collection
    {
        $query = WaLog::query()->latest();

        if ($this->from && $this->to) {
            $query->whereBetween('created_at', [
                $this->from . ' 00:00:00',
                $this->to . ' 23:59:59',
            ]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Case ID',
            'Customer',
            'PIC',
            'Nomor',
            'Pesan',
            'Status',
            'Tanggal',
        ];
    }

    public function map($wa): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $wa->case_id ?? '-',
            $wa->customer_name ?? '-',
            $wa->pic ?? '-',
            $wa->customer_phone ?? '-',
            $wa->message ?? '-',
            $wa->status ?? '-',
            optional($wa->created_at)->format('d M Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $lastRow = max($sheet->getHighestRow(), 1);

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:H' . $lastRow);

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:H1')->getAlignment()->setVertical('center');

        $widths = [
            'A' => 5,
            'B' => 11,
            'C' => 20,
            'D' => 10,
            'E' => 15,
            'F' => 70,
            'G' => 15,
            'H' => 22,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:H' . $lastRow)
            ->getAlignment()
            ->setVertical('top');

         // Kolom E = Number
    $sheet->getStyle('E2:E' . $lastRow)
        ->getNumberFormat()
        ->setFormatCode('0');

        $sheet->getStyle('F2:F' . $lastRow)
            ->getAlignment()
            ->setWrapText(false);
    }
}
