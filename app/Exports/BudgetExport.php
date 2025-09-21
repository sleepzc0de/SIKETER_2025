<?php

namespace App\Exports;

use App\Models\BudgetCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BudgetExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = BudgetCategory::query();

        if (!empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        if (!empty($this->filters['pic'])) {
            $query->byPIC($this->filters['pic']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Kegiatan',
            'KRO',
            'RO',
            'Inisial',
            'Akun',
            'Program/Kegiatan/Output',
            'PIC',
            'Pagu Anggaran',
            'Referensi',
            'Referensi 2',
            'Referensi Output',
            'Length',
            'Realisasi Jan',
            'Realisasi Feb',
            'Realisasi Mar',
            'Realisasi Apr',
            'Realisasi Mei',
            'Realisasi Jun',
            'Realisasi Jul',
            'Realisasi Agu',
            'Realisasi Sep',
            'Realisasi Okt',
            'Realisasi Nov',
            'Realisasi Des',
            'Tagihan Outstanding',
            'Total Penyerapan',
            'Sisa Anggaran',
            'Persentase Realisasi (%)',
        ];
    }

    public function map($budget): array
    {
        return [
            $budget->kegiatan,
            $budget->kro_code,
            $budget->ro_code,
            $budget->initial_code,
            $budget->account_code,
            $budget->program_kegiatan_output,
            $budget->pic,
            $budget->budget_allocation,
            $budget->reference,
            $budget->reference2,
            $budget->reference_output,
            $budget->length,
            $budget->realisasi_jan,
            $budget->realisasi_feb,
            $budget->realisasi_mar,
            $budget->realisasi_apr,
            $budget->realisasi_mei,
            $budget->realisasi_jun,
            $budget->realisasi_jul,
            $budget->realisasi_agu,
            $budget->realisasi_sep,
            $budget->realisasi_okt,
            $budget->realisasi_nov,
            $budget->realisasi_des,
            $budget->tagihan_outstanding,
            $budget->total_penyerapan,
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
}
