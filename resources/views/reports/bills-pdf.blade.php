<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tagihan</title>
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
            padding: 6px;
            text-align: left;
            font-size: 10px;
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
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .status-sp2d {
            background-color: #d1fae5;
            color: #065f46;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TAGIHAN</h1>
        <h2>Biro Manajemen BMN dan Pengadaan</h2>
        <h2>Kementerian Keuangan Republik Indonesia</h2>
        <p>Periode: {{ $year }}{{ $month ? ' - ' . DateTime::createFromFormat('!m', $month)->format('F') : '' }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Tagihan</td>
                <td class="label">SP2D</td>
                <td class="label">Pending</td>
                <td class="label">Dibatalkan</td>
                <td class="label">Nilai SP2D</td>
                <td class="label">Nilai Pending</td>
            </tr>
            <tr>
                <td class="text-center">{{ $summary['total_bills'] }}</td>
                <td class="text-center">{{ $summary['sp2d_count'] }}</td>
                <td class="text-center">{{ $summary['pending_count'] }}</td>
                <td class="text-center">{{ $summary['cancelled_count'] }}</td>
                <td class="text-center">Rp {{ number_format($summary['sp2d_amount'], 0, ',', '.') }}</td>
                <td class="text-center">Rp {{ number_format($summary['pending_amount'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Tagihan</th>
                <th>Kode Anggaran</th>
                <th>Periode</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Dibuat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bills as $index => $bill)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $bill->bill_number }}</td>
                <td>{{ $bill->budgetCategory->full_code }}</td>
                <td>{{ $bill->month_name }} {{ $bill->year }}</td>
                <td class="text-right">{{ number_format($bill->amount, 0, ',', '.') }}</td>
                <td class="text-center">
                    <span class="status-{{ $bill->status }}">
                        @if($bill->status === 'pending') Pending
                        @elseif($bill->status === 'sp2d') SP2D
                        @else Dibatalkan
                        @endif
                    </span>
                </td>
                <td>{{ $bill->creator->name }}</td>
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
