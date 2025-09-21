<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Perbandingan Bulanan</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PERBANDINGAN BULANAN</h1>
        <h2>Biro Manajemen BMN dan Pengadaan</h2>
        <h2>Kementerian Keuangan Republik Indonesia</h2>
        <p>Tahun: {{ $year }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Realisasi (Rp)</th>
                <th>Total Tagihan</th>
                <th>SP2D</th>
                <th>Pending</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyData as $data)
            <tr>
                <td>{{ $data['month_name'] }}</td>
                <td class="text-right">{{ number_format($data['realization'], 0, ',', '.') }}</td>
                <td class="text-center">{{ $data['bills_count'] }}</td>
                <td class="text-center">{{ $data['sp2d_count'] }}</td>
                <td class="text-center">{{ $data['pending_count'] }}</td>
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
