<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaksi->nomor_transaksi }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #000;
            background: #eee;
            margin: 0;
            padding: 16px;
        }
        .struk {
            width: 80mm;
            margin: 0 auto;
            background: #fff;
            padding: 10px 8px;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .garis { border-top: 1px dashed #000; margin: 6px 0; }
        .baris { display: flex; justify-content: space-between; gap: 8px; }
        .item { margin-bottom: 5px; }
        .judul { font-size: 15px; font-weight: bold; }
        .aksi { text-align: center; margin-top: 14px; font-family: Arial, sans-serif; }
        .aksi a, .aksi button {
            display: inline-block;
            padding: 8px 14px;
            margin: 0 3px;
            border: 1px solid #333;
            border-radius: 6px;
            background: #fff;
            color: #000;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
        }
        .aksi .utama { background: #333; color: #fff; }

        @page { size: 80mm auto; margin: 0; }
        @media print {
            body { background: #fff; padding: 0; }
            .struk { width: 100%; padding: 4px; }
            .aksi { display: none; }
        }
    </style>
</head>
<body>
    @php
        // Gabungkan baris dengan SKU yang sama (FIFO bisa memecah satu barang ke beberapa batch)
        $barang = $transaksi->items->groupBy('nomor_sku')->map(function ($baris) {
            $pertama = $baris->first();
            return [
                'nama'     => trim($pertama->produk . ' ' . $pertama->getAttribute('varian')),
                'qty'      => $baris->sum('qty'),
                'harga'    => $pertama->harga,
                'sub_total'=> $baris->sum('sub_total'),
            ];
        })->values();
    @endphp

    <div class="struk">
        <div class="center">
            <div class="judul">{{ env('APP_NAME', 'SIBENGKEL') }}</div>
            <div>Struk Penjualan</div>
        </div>

        <div class="garis"></div>

        <div class="baris"><span>No</span><span>{{ $transaksi->nomor_transaksi }}</span></div>
        <div class="baris"><span>Tanggal</span><span>{{ $transaksi->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="baris"><span>Kasir</span><span>{{ $transaksi->petugas }}</span></div>
        @if ($transaksi->penerima)
            <div class="baris"><span>Pelanggan</span><span>{{ $transaksi->penerima }}</span></div>
        @endif

        <div class="garis"></div>

        @foreach ($barang as $b)
            <div class="item">
                <div>{{ $b['nama'] }}</div>
                <div class="baris">
                    <span>{{ number_format($b['qty']) }} x {{ number_format($b['harga']) }}</span>
                    <span>{{ number_format($b['sub_total']) }}</span>
                </div>
            </div>
        @endforeach

        <div class="garis"></div>

        <div class="baris bold">
            <span>TOTAL</span>
            <span>Rp {{ number_format($transaksi->total_harga) }}</span>
        </div>
        <div class="baris">
            <span>Jumlah barang</span>
            <span>{{ number_format($barang->sum('qty')) }} pcs</span>
        </div>

        @if ($transaksi->keterangan)
            <div class="garis"></div>
            <div>Ket: {{ $transaksi->keterangan }}</div>
        @endif

        <div class="garis"></div>
        <div class="center">
            Terima kasih<br>
            Barang yang sudah dibeli<br>
            harap diperiksa kembali
        </div>
    </div>

    <div class="aksi">
        <button type="button" class="utama" onclick="window.print()">Cetak Ulang</button>
        <a href="{{ route('transaksi-keluar.create') }}">Transaksi Baru</a>
        <a href="{{ route('transaksi-keluar.index') }}">Data Transaksi</a>
    </div>

    <script>
        // Dialog cetak otomatis hanya saat struk dibuka tepat setelah transaksi disimpan
        if (new URLSearchParams(location.search).get('cetak') === '1') {
            window.addEventListener('load', function () { setTimeout(window.print, 300); });
        }
    </script>
</body>
</html>
