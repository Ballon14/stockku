<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Karyawan</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background-color: #f4f4f4; text-align: center; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN ABSENSI KARYAWAN</div>
        <div class="subtitle">StockKu - Toko Serba Ada</div>
        <div class="subtitle">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-left">Karyawan</th>
                <th class="text-left">Jabatan</th>
                <th>Total Hari</th>
                <th>Hadir</th>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Cuti</th>
                <th>Alpha</th>
                <th>Presentase</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td class="text-left">{{ $row['employee_name'] }}</td>
                <td class="text-left">{{ $row['employee_jabatan'] }}</td>
                <td class="text-center">{{ $row['total_days'] }}</td>
                <td class="text-center">{{ $row['hadir'] }}</td>
                <td class="text-center">{{ $row['sakit'] }}</td>
                <td class="text-center">{{ $row['izin'] }}</td>
                <td class="text-center">{{ $row['cuti'] }}</td>
                <td class="text-center">{{ $row['alpha'] }}</td>
                <td class="text-center">{{ $row['attendance_percentage'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right;">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Oleh: {{ auth()->user()->name }}</p>
    </div>
</body>
</html>
