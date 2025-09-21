<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Realisasi Anggaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: normal;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary .label {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN REALISASI ANGGARAN</h1>
        <h2>Biro Manajemen BMN dan Pengadaan</h2>
        <h2>Kementerian Keuangan Republik Indonesia</h2>
        <p>Periode: {{ $year }}{{ $month ? ' - ' . DateTime::createFromFormat('!m', $month)->format('F') : '' }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Anggaran</td>
                <td class="label">Total Realisasi</td>
                <td class="label">Outstanding</td>
                <td class="label">Persentase</td>
            </tr>
            <tr>
                <td class="text-center">Rp {{ number_format($summary['total_budget'], 0, ',', '.') }}</td>
                <td class="text-center">Rp {{ number_format($summary['total_realization'], 0, ',', '.') }}</td>
                <td class="text-center">Rp {{ number_format($summary['total_outstanding'], 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($summary['realization_percentage'], 1) }}%</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Deskripsi</th>
                <th>PIC</th>
                <th>Pagu Anggaran</th>
                <th>Realisasi</th>
                <th>Outstanding</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgets as $index => $budget)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $budget->full_code }}</td>
                <td>{{ $budget->program_kegiatan_output }}</td>
                <td>{{ $budget->pic }}</td>
                <td class="text-right">{{ number_format($budget->budget_allocation, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($budget->total_penyerapan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($budget->tagihan_outstanding, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($budget->realization_percentage, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Jakarta, {{ date('d F Y') }}</p>
        <br><br><br>
        <p>Kepala Biro Manajemen BMN dan Pengadaan</p>
    </div>
</body>
</html>
