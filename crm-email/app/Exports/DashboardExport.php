<?php

namespace App\Exports;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected ?string $from;
    protected ?string $to;

    public function __construct(?string $from = null, ?string $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function collection(): Collection
    {
        $query = EmailLog::query()->latest();

        if ($this->from && $this->to) {
            $query->whereBetween('created_at', [
                $this->from . ' 00:00:00',
                $this->to . ' 23:59:59',
            ]);
        }

        return $query->get();
    }


    public function title(): string
    {
        return 'Detail Email';
    }

    public function headings(): array
    {
        return [
            'No',
            'Case ID',
            'PIC',
            'Customer',
            'Email',
            'Subject',
            'Tanggal',
        ];
    }

    public function map($email): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $email->case_id ?? '-',
            $email->pic ?? '-',
            $email->customer_name ?? '-',
            $email->customer_email ?? '-',
            $email->subject ?? '-',
            optional($email->created_at)->format('d M Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $lastRow = max($sheet->getHighestRow(), 1);

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:G' . $lastRow);

        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:G1')->getAlignment()->setVertical('center');

        $widths = [
            'A' => 5,
            'B' => 15,
            'C' => 20,
            'D' => 20,
            'E' => 38,
            'F' => 55,
            'G' => 22,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:G' . $lastRow)
            ->getAlignment()
            ->setVertical('top');

        $sheet->getStyle('F2:F' . $lastRow)
            ->getAlignment()
            ->setWrapText(false);
    }
}
