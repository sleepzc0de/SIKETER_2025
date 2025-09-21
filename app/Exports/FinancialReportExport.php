<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $budgets;
    protected $summary;

    public function __construct($budgets, $summary)
    {
        $this->budgets = $budgets;
        $this->summary = $summary;
    }

    public function collection()
    {
        return $this->budgets;
    }

    public function headings(): array
    {
        return [
            'Kode Lengkap',
            'Deskripsi',
            'PIC',
            'Pagu Anggaran',
            'Total Realisasi',
            'Tagihan Outstanding',
            'Sisa Anggaran',
            'Persentase Realisasi (%)',
        ];
    }

    public function map($budget): array
    {
        return [
            $budget->full_code,
            $budget->program_kegiatan_output,
            $budget->pic,
            $budget->budget_allocation,
            $budget->total_penyerapan,
            $budget->tagihan_outstanding,
            $budget->sisa_anggaran,
            $budget->realization_percentage,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Realisasi Anggaran';
    }
}
