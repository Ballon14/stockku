<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Perubahan Harga</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; }
        th { background-color: #f4f4f4; text-align: left; font-size: 9px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary-box { margin-bottom: 20px; background: #f9f9f9; padding: 10px 12px; border-radius: 5px; border: 1px solid #ddd; }
        .summary-grid { width: 100%; margin-top: 0; }
        .summary-grid td { border: none; padding: 4px 8px; font-size: 10px; }
        .badge-naik { background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; }
        .badge-turun { background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; }
        .text-naik { color: #059669; font-weight: bold; }
        .text-turun { color: #dc2626; font-weight: bold; }
        .line-through { text-decoration: line-through; color: #999; }
        .alert-box { margin-bottom: 20px; background: #fffbeb; padding: 10px 12px; border-radius: 5px; border: 1px solid #fbbf24; }
        .alert-title { font-weight: bold; color: #92400e; margin-bottom: 6px; font-size: 11px; }
        .alert-text { color: #78350f; font-size: 9px; margin-bottom: 6px; }
        .col-jual { background-color: #f0fdf4; }
    </style>
</head>
<body>
    <div class="header" style="position: relative; padding-top: 10px; min-height: 80px;">
        @if(file_exists(public_path('logo.png')))
            <img src="{{ public_path('logo.png') }}" alt="Logo" style="position: absolute; left: 0; top: 10px; height: 70px; width: 70px; object-fit: contain; filter: invert(1);">
        @endif
        
        <div style="text-align: center;">
            <div class="title" style="font-size: 20px; font-weight: bold; margin-bottom: 3px;">TOKO MAKMUR</div>
            <div class="subtitle" style="font-size: 10px; margin-bottom: 2px;">Kaliboto, Kec. Bener, Kabupaten Purworejo, Jawa Tengah, Indonesia</div>
            <div class="subtitle" style="font-size: 10px; margin-bottom: 12px;">Telp/WA: +62 821-3583-0272</div>
            
            <div class="title" style="font-size: 15px; border-top: 1px dashed #ccc; padding-top: 12px; margin-top: 8px;">REKAP PERUBAHAN HARGA BELI & HARGA JUAL</div>
            <div class="subtitle" style="font-size: 10px;">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="summary-box">
        <table class="summary-grid">
            <tr>
                <td><strong>Total Perubahan Harga Beli:</strong><br>{{ $data['summary']['total_changes'] }} kali</td>
                <td><strong>Harga Beli Naik:</strong><br><span class="text-naik">{{ $data['summary']['total_naik'] }}</span></td>
                <td><strong>Harga Beli Turun:</strong><br><span class="text-turun">{{ $data['summary']['total_turun'] }}</span></td>
                <td><strong>Produk Terpengaruh:</strong><br>{{ $data['summary']['products_affected'] }} produk</td>
            </tr>
        </table>
    </div>

    @if($data['current_vs_last_bought']->isNotEmpty())
    <div class="alert-box">
        <div class="alert-title">⚠ Harga Beli Aktual (Restock) Berbeda dengan Master Data</div>
        <div class="alert-text">Produk berikut baru saja dibeli dari supplier dengan harga berbeda dari master data sistem. Pastikan harga jual ke customer menghasilkan margin keuntungan yang memadai.</div>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kode/SKU</th>
                    <th class="text-right">Harga Beli Master</th>
                    <th class="text-right">Harga Beli Restock</th>
                    <th class="text-right">Selisih Modal</th>
                    <th class="text-right" style="background-color: #eff6ff;">Harga Jual (Customer)</th>
                    <th class="text-right">Margin Restock</th>
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
                    <td class="text-right" style="font-weight: bold; color: #1e40af; background-color: #eff6ff;">
                        Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                    </td>
                    <td class="text-right {{ $item->margin >= 0 ? 'text-naik' : 'text-turun' }}">
                        {{ $item->margin >= 0 ? '+' : '' }}Rp {{ number_format($item->margin, 0, ',', '.') }} ({{ $item->margin_persen }}%)
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

    <h3 style="margin-top: 15px; margin-bottom: 5px;">Riwayat Perubahan Harga Beli & Penjualan</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Kategori</th>
                <th class="text-right">Harga Beli Lama</th>
                <th class="text-right">Harga Beli Baru</th>
                <th class="text-right">Selisih Modal</th>
                <th class="text-center">Status</th>
                <th class="text-right" style="background-color: #eff6ff;">Harga Jual Customer</th>
                <th class="text-right">Margin Untung</th>
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
                    <br><span style="font-size: 8px; color: #888;">{{ $change->product_sku }}</span>
                </td>
                <td>{{ $change->category_name }}</td>
                <td class="text-right line-through">Rp {{ number_format($change->harga_lama, 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($change->harga_baru, 0, ',', '.') }}</td>
                <td class="text-right {{ $change->tipe === 'naik' ? 'text-naik' : 'text-turun' }}">
                    {{ $change->tipe === 'naik' ? '+' : '' }}Rp {{ number_format($change->selisih, 0, ',', '.') }}
                    <br><span style="font-size: 8px;">({{ $change->tipe === 'naik' ? '+' : '' }}{{ $change->persen }}%)</span>
                </td>
                <td class="text-center">
                    <span class="{{ $change->tipe === 'naik' ? 'badge-naik' : 'badge-turun' }}">
                        {{ $change->tipe === 'naik' ? '↑ Naik' : '↓ Turun' }}
                    </span>
                </td>
                <td class="text-right" style="font-weight: bold; color: #1e40af; background-color: #eff6ff;">
                    Rp {{ number_format($change->harga_jual, 0, ',', '.') }}
                </td>
                <td class="text-right {{ $change->margin >= 0 ? 'text-naik' : 'text-turun' }}">
                    {{ $change->margin >= 0 ? '+' : '' }}Rp {{ number_format($change->margin, 0, ',', '.') }}
                    <br><span style="font-size: 8px;">({{ $change->margin_persen }}%)</span>
                </td>
                <td class="text-center" style="font-size: 8px;">{{ $change->sumber }}</td>
                <td style="font-size: 8px; white-space: nowrap;">{{ $change->invoice_perubahan }}</td>
                <td>{{ $change->pencatat }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center" style="padding: 20px; color: #999;">Tidak ada perubahan harga pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Oleh: {{ auth()->user()->name }}</p>
    </div>
</body>
</html>
