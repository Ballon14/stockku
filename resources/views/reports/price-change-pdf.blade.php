<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Perubahan Harga</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 5px 7px; }
        th { background-color: #f4f4f4; text-align: left; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary-box { margin-bottom: 20px; background: #f9f9f9; padding: 12px; border-radius: 5px; border: 1px solid #ddd; }
        .summary-grid { width: 100%; margin-top: 0; }
        .summary-grid td { border: none; padding: 4px 8px; }
        .badge-naik { background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 8px; font-size: 9px; font-weight: bold; }
        .badge-turun { background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 8px; font-size: 9px; font-weight: bold; }
        .badge-beli { background: #e0e7ff; color: #3730a3; padding: 2px 6px; border-radius: 6px; font-size: 9px; font-weight: bold; }
        .badge-jual { background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 6px; font-size: 9px; font-weight: bold; }
        .text-naik { color: #059669; font-weight: bold; }
        .text-turun { color: #dc2626; font-weight: bold; }
        .line-through { text-decoration: line-through; color: #999; }
        .alert-box { margin-bottom: 20px; background: #fffbeb; padding: 10px 12px; border-radius: 5px; border: 1px solid #fbbf24; }
        .alert-title { font-weight: bold; color: #92400e; margin-bottom: 6px; font-size: 12px; }
        .alert-text { color: #78350f; font-size: 10px; margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="header" style="position: relative; padding-top: 10px; min-height: 80px;">
        @if(file_exists(public_path('logo.png')))
            <img src="{{ public_path('logo.png') }}" alt="Logo" style="position: absolute; left: 0; top: 10px; height: 70px; width: 70px; object-fit: contain; filter: invert(1);">
        @endif
        
        <div style="text-align: center;">
            <div class="title" style="font-size: 22px; font-weight: bold; margin-bottom: 3px;">TOKO MAKMUR</div>
            <div class="subtitle" style="font-size: 11px; margin-bottom: 2px;">Kaliboto, Kec. Bener, Kabupaten Purworejo, Jawa Tengah, Indonesia</div>
            <div class="subtitle" style="font-size: 11px; margin-bottom: 15px;">Telp/WA: +62 821-3583-0272</div>
            
            <div class="title" style="font-size: 16px; border-top: 1px dashed #ccc; padding-top: 15px; margin-top: 10px;">REKAP PERUBAHAN HARGA</div>
            <div class="subtitle">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} | Tipe: {{ ($tipeHarga ?? '') === 'beli' ? 'Harga Beli' : (($tipeHarga ?? '') === 'jual' ? 'Harga Jual' : 'Semua (Beli & Jual)') }}</div>
        </div>
    </div>

    <div class="summary-box">
        <table class="summary-grid">
            <tr>
                <td><strong>Total Perubahan:</strong><br>{{ $data['summary']['total_changes'] }} <span style="font-size: 10px; color: #666;">({{ $data['summary']['total_beli'] ?? 0 }} Beli / {{ $data['summary']['total_jual'] ?? 0 }} Jual)</span></td>
                <td><strong>Harga Naik:</strong><br><span class="text-naik">{{ $data['summary']['total_naik'] }}</span></td>
                <td><strong>Harga Turun:</strong><br><span class="text-turun">{{ $data['summary']['total_turun'] }}</span></td>
                <td><strong>Produk Terpengaruh:</strong><br>{{ $data['summary']['products_affected'] }}</td>
            </tr>
        </table>
    </div>

    @if(($tipeHarga ?? '') !== 'jual' && $data['current_vs_last_bought']->isNotEmpty())
    <div class="alert-box">
        <div class="alert-title">⚠ Harga Beli Aktual (Restock) Berbeda dengan Master Data</div>
        <div class="alert-text">Produk berikut baru saja dibeli dari supplier dengan harga yang berbeda dari harga beli standar (Master Data) di sistem.</div>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kode/SKU</th>
                    <th class="text-right">Harga Master (Sistem)</th>
                    <th class="text-right">Harga Aktual (Restock)</th>
                    <th class="text-right">Selisih</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['current_vs_last_bought'] as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->product_sku }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga_beli_sekarang, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($item->harga_terakhir_dibeli, 0, ',', '.') }}</td>
                    <td class="text-right {{ $item->tipe === 'naik' ? 'text-naik' : 'text-turun' }}">
                        {{ $item->tipe === 'naik' ? '+' : '' }}Rp {{ number_format($item->selisih, 0, ',', '.') }} ({{ $item->tipe === 'naik' ? '+' : '' }}{{ $item->persen }}%)
                    </td>
                    <td class="text-center">
                        <span class="{{ $item->tipe === 'naik' ? 'badge-naik' : 'badge-turun' }}">
                            {{ $item->tipe === 'naik' ? '↑ Naik' : '↓ Turun' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <h3 style="margin-top: 20px; margin-bottom: 5px;">Riwayat Perubahan Harga</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Kategori</th>
                <th class="text-center">Tipe</th>
                <th class="text-right">Harga Lama</th>
                <th class="text-right">Harga Baru</th>
                <th class="text-right">Selisih</th>
                <th class="text-center">Status</th>
                <th class="text-center">Sumber</th>
                <th>Invoice/Ref</th>
                <th>Pencatat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['changes'] as $i => $change)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td style="white-space: nowrap;">{{ $change->tanggal->format('d/m/Y H:i') }}</td>
                <td>
                    {{ $change->product_name }}
                    <br><span style="font-size: 9px; color: #888;">{{ $change->product_sku }}</span>
                </td>
                <td>{{ $change->category_name }}</td>
                <td class="text-center">
                    @if(($change->tipe_harga ?? 'beli') === 'jual')
                        <span class="badge-jual">Jual</span>
                    @else
                        <span class="badge-beli">Beli</span>
                    @endif
                </td>
                <td class="text-right line-through">Rp {{ number_format($change->harga_lama, 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($change->harga_baru, 0, ',', '.') }}</td>
                <td class="text-right {{ $change->tipe === 'naik' ? 'text-naik' : 'text-turun' }}">
                    {{ $change->tipe === 'naik' ? '+' : '' }}Rp {{ number_format($change->selisih, 0, ',', '.') }}
                    <br><span style="font-size: 9px;">({{ $change->tipe === 'naik' ? '+' : '' }}{{ $change->persen }}%)</span>
                </td>
                <td class="text-center">
                    <span class="{{ $change->tipe === 'naik' ? 'badge-naik' : 'badge-turun' }}">
                        {{ $change->tipe === 'naik' ? '↑ Naik' : '↓ Turun' }}
                    </span>
                </td>
                <td class="text-center" style="font-size: 9px;">{{ $change->sumber }}</td>
                <td style="font-size: 9px; white-space: nowrap;">{{ $change->invoice_perubahan }}</td>
                <td>{{ $change->pencatat }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 20px; color: #999;">Tidak ada perubahan harga pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 40px; text-align: right;">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Oleh: {{ auth()->user()->name }}</p>
    </div>
</body>
</html>
