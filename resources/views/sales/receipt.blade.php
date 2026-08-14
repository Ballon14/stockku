<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk - {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 300px; margin: 0 auto; padding: 10px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        .double-line { border-top: 2px double #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .logo { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        @media print { body { width: 80mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="center">
        <div class="logo">StokCku</div>
        <div>Toko Serba Ada</div>
        <div style="font-size: 10px;">Jl. Contoh No. 123, Kota</div>
    </div>
    <div class="double-line"></div>
    <table>
        <tr><td>No</td><td>: {{ $sale->invoice_number }}</td></tr>
        <tr><td>Tgl</td><td>: {{ $sale->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td>: {{ $sale->user->name }}</td></tr>
    </table>
    <div class="line"></div>
    @foreach($sale->items as $item)
    <div>
        <div class="bold">{{ $item->product->name }}</div>
        <table>
            <tr>
                <td>{{ $item->qty }} x Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    @endforeach
    <div class="line"></div>
    <table>
        <tr><td>Subtotal</td><td class="right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td></tr>
        @if($sale->diskon > 0)
        <tr><td>Diskon</td><td class="right">- Rp {{ number_format($sale->diskon, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="bold"><td>TOTAL</td><td class="right">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td></tr>
        <tr><td>Bayar</td><td class="right">Rp {{ number_format($sale->bayar, 0, ',', '.') }}</td></tr>
        <tr class="bold"><td>Kembalian</td><td class="right">Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</td></tr>
    </table>
    <div class="double-line"></div>
    <div class="center" style="font-size: 10px; margin-top: 10px;">
        Terima kasih atas kunjungan Anda!<br>
        Barang yang sudah dibeli<br>
        tidak dapat ditukar/dikembalikan
    </div>
    <div class="no-print center" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14px; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer;">🖨️ Print Struk</button>
        <button onclick="window.close()" style="padding: 10px 30px; font-size: 14px; background: #6b7280; color: white; border: none; border-radius: 8px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>
</body>
</html>
