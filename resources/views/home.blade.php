@extends('layouts.kai')

@section('content')
@php
    $angka = fn ($n) => number_format($n, 0, ',', '.');
    $rp    = fn ($n) => 'Rp ' . number_format(abs($n), 0, ',', '.');
    $margin = $biayaDiterima - $biayaKeluar;

    $svg = fn ($path) => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
    $ikon = [
        'transfer' => $svg('<path d="M17 4l3 3-3 3"/><path d="M20 7H8"/><path d="M7 20l-3-3 3-3"/><path d="M4 17h12"/>'),
        'masuk'    => $svg('<path d="M12 5v14"/><path d="M6 13l6 6 6-6"/>'),
        'keluar'   => $svg('<path d="M12 19V5"/><path d="M6 11l6-6 6 6"/>'),
        'stok'     => $svg('<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 10h16M10 4v16"/>'),
        'box'      => $svg('<path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>'),
    ];

    $kartu = [
        ['label' => 'Total Transaksi', 'nilai' => $angka($totalTransaksi), 'unit' => null,     'tren' => $tren['transaksi'], 'ikon' => 'transfer', 'tema' => 'ungu'],
        ['label' => 'Barang Masuk',    'nilai' => $angka($barangMasuk),    'unit' => 'unit',   'tren' => $tren['masuk'],     'ikon' => 'masuk',    'tema' => 'oranye'],
        ['label' => 'Barang Keluar',   'nilai' => $angka($barangKeluar),   'unit' => 'unit',   'tren' => $tren['keluar'],    'ikon' => 'keluar',   'tema' => 'hijau'],
        ['label' => 'Stok Menipis',    'nilai' => str_pad($stokMenipis, 2, '0', STR_PAD_LEFT), 'unit' => 'produk', 'tren' => false, 'ikon' => 'stok', 'tema' => 'biru'],
    ];
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .sb {
        --ink: #1e2233;   --muted: #8b90a3;  --line: #eceef4;
        --ungu: #6c5ce7;  --ungu-soft: #eeebff;
        --oranye: #ffa24c; --oranye-soft: #fff1e0;
        --hijau: #2fb67c; --hijau-soft: #e2f7ee;
        --biru: #4a7be8;  --biru-soft: #e7eefd;
        --merah: #e5484d; --merah-soft: #fde8e8;
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        color: var(--ink);
        padding: 8px 0 32px;
    }
    .sb * { box-sizing: border-box; }
    .sb h1, .sb h2, .sb p { margin: 0; }

    /* header */
    .sb-head { display: flex; flex-wrap: wrap; gap: 14px; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .sb-head h1 { font-size: 1.6rem; font-weight: 800; letter-spacing: -0.02em; }
    .sb-head p  { margin-top: 6px; font-size: .8rem; color: var(--muted); }
    .sb-periode { display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid var(--line);
                  border-radius: 10px; padding: 8px 12px; font-size: .75rem; color: var(--muted); }
    .sb-periode input { border: 0; background: transparent; font: inherit; color: var(--ink); width: 112px; padding: 0; }
    .sb-periode input:focus { outline: 0; }
    .sb-periode button { border: 0; background: var(--ungu); color: #fff; border-radius: 6px; padding: 4px 10px; font: inherit; font-weight: 600; }

    /* banner */
    .sb-banner { position: relative; overflow: hidden; border-radius: 16px; padding: 28px 22px; margin-bottom: 20px; color: #fff;
                 background: linear-gradient(100deg, #6c5ce7 0%, #8479f2 100%); }
    .sb-banner::before, .sb-banner::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255,255,255,.1); }
    .sb-banner::before { width: 260px; height: 260px; right: -40px; top: -90px; }
    .sb-banner::after  { width: 170px; height: 170px; right: 150px; top: 40px; background: rgba(255,255,255,.06); }
    .sb-banner h2 { font-size: 1.05rem; font-weight: 700; position: relative; }
    .sb-banner p  { margin-top: 10px; font-size: .78rem; opacity: .92; position: relative; }

    /* cards */
    .sb-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 2px rgba(30,34,51,.04), 0 6px 20px rgba(30,34,51,.04); }

    .sb-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 20px; }
    .sb-stat { padding: 20px 22px 22px; }
    .sb-stat-top { display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; }
    .sb-ikon { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; }
    .sb-ikon.ungu   { background: var(--ungu-soft);   color: var(--ungu); }
    .sb-ikon.oranye { background: var(--oranye-soft); color: var(--oranye); }
    .sb-ikon.hijau  { background: var(--hijau-soft);  color: var(--hijau); }
    .sb-ikon.biru   { background: var(--biru-soft);   color: var(--biru); }
    .sb-tren { font-size: .7rem; font-weight: 700; }
    .sb-tren.naik  { color: var(--hijau); }
    .sb-tren.turun { color: var(--merah); }
    .sb-tren.info  { color: var(--oranye); }
    .sb-stat small { display: block; font-size: .72rem; color: var(--muted); margin-bottom: 4px; }
    .sb-nilai { font-size: 1.7rem; font-weight: 800; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; }
    .sb-nilai span { font-size: .72rem; font-weight: 500; color: var(--muted); margin-left: 2px; letter-spacing: 0; }

    .sb-main > * { min-width: 0; }
    #chart-transaksi { min-height: 300px; }
    .sb-main { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; margin-bottom: 20px; }
    .sb-panel { padding: 22px 24px; }
    .sb-panel-head { display: flex; justify-content: space-between; align-items: start; margin-bottom: 14px; gap: 12px; }
    .sb-panel-head h2 { font-size: .95rem; font-weight: 700; }
    .sb-panel-head p  { margin-top: 6px; font-size: .72rem; color: var(--muted); }
    .sb-legend { display: flex; gap: 14px; font-size: .7rem; color: var(--muted); white-space: nowrap; }
    .sb-legend i { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 5px; }
    .sb-link { font-size: .72rem; font-weight: 700; color: var(--ungu); text-decoration: none; white-space: nowrap; }

    /* daftar stok */
    .sb-list { list-style: none; margin: 8px 0 0; padding: 0; }
    .sb-list li { display: flex; align-items: center; gap: 12px; padding: 12px 0; }
    .sb-list .sb-thumb { width: 34px; height: 34px; border-radius: 10px; background: #f2f3f8; color: #9aa0b4; display: grid; place-items: center; flex: none; }
    .sb-list .sb-thumb svg { width: 16px; height: 16px; }
    .sb-list .sb-nama { flex: 1; min-width: 0; }
    .sb-list b { display: block; font-size: .8rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sb-list small { font-size: .68rem; color: var(--muted); }
    .sb-pill { font-size: .68rem; font-weight: 600; padding: 4px 10px; border-radius: 999px; background: var(--oranye-soft); color: #e08a2e; white-space: nowrap; }
    .sb-pill.habis { background: var(--merah-soft); color: var(--merah); }
    .sb-kosong { padding: 28px 0; text-align: center; font-size: .8rem; color: var(--muted); }

    /* keuangan + tabel */
    .sb-uang { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 20px; }
    .sb-uang .sb-card { padding: 20px 24px; }
    .sb-uang small { font-size: .72rem; color: var(--muted); }
    .sb-uang .sb-nilai { font-size: 1.3rem; margin-top: 6px; }
    .sb-uang .plus  { color: var(--hijau); }
    .sb-uang .minus { color: var(--merah); }

    .sb-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    .sb-table th { text-align: left; font-weight: 600; font-size: .7rem; color: var(--muted); padding: 10px 0; border-bottom: 1px solid var(--line); }
    .sb-table td { padding: 12px 0; border-bottom: 1px solid var(--line); }
    .sb-table tr:last-child td { border-bottom: 0; }
    .sb-table .r { text-align: right; }
    .sb-table small { color: var(--muted); font-size: .68rem; }
    .sb-scroll { overflow-x: auto; }

    @media (max-width: 1100px) { .sb-stats { grid-template-columns: repeat(2, 1fr); } .sb-main { grid-template-columns: 1fr; } }
    @media (max-width: 640px)  { .sb-stats, .sb-uang { grid-template-columns: 1fr; } }
</style>

<div class="sb container-fluid">

    <div class="sb-head">
        <div>
            <h1>Dashboard Analitik</h1>
            <p>Ringkasan aktivitas dan kondisi bengkel Anda.</p>
        </div>
        <form method="GET" class="sb-periode">
            <span>Periode:</span>
            <input type="date" name="start" value="{{ $start }}" aria-label="Tanggal mulai">
            <span>–</span>
            <input type="date" name="end" value="{{ $end }}" aria-label="Tanggal akhir">
            <button type="submit">Terapkan</button>
        </form>
    </div>

    <div class="sb-banner">
        <h2>Selamat datang kembali! 👋</h2>
        <p>Pantau performa usaha dan persediaan produk Anda dalam satu tempat.</p>
    </div>

    {{-- 4 kartu ringkasan --}}
    <div class="sb-stats">
        @foreach ($kartu as $k)
            <div class="sb-card sb-stat">
                <div class="sb-stat-top">
                    <div class="sb-ikon {{ $k['tema'] }}">{!! $ikon[$k['ikon']] !!}</div>

                    @if ($k['tren'] === false)
                        <span class="sb-tren info">Perlu dipantau</span>
                    @elseif ($k['tren'] !== null)
                        <span class="sb-tren {{ $k['tren'] >= 0 ? 'naik' : 'turun' }}">
                            {{ $k['tren'] >= 0 ? '↗' : '↘' }} {{ number_format(abs($k['tren']), 1, ',', '.') }}%
                        </span>
                    @endif
                </div>
                <small>{{ $k['label'] }}</small>
                <div class="sb-nilai">{{ $k['nilai'] }}@if($k['unit'])<span>{{ $k['unit'] }}</span>@endif</div>
            </div>
        @endforeach
    </div>

    {{-- Grafik + stok --}}
    <div class="sb-main">
        <section class="sb-card sb-panel">
            <div class="sb-panel-head">
                <div>
                    <h2>Ringkasan Transaksi</h2>
                    <p>Perbandingan barang masuk dan keluar</p>
                </div>
                <div class="sb-legend">
                    <span><i style="background:var(--ungu)"></i>Masuk</span>
                    <span><i style="background:var(--oranye)"></i>Keluar</span>
                </div>
            </div>
            @if (array_sum($grafik['masuk']) + array_sum($grafik['keluar']) > 0)
                <div id="chart-transaksi"></div>
            @else
                <div class="sb-kosong">Belum ada transaksi dalam 6 bulan terakhir (sampai {{ \Carbon\Carbon::parse($end)->translatedFormat('F Y') }}).</div>
            @endif
        </section>

        <section class="sb-card sb-panel">
            <div class="sb-panel-head">
                <div>
                    <h2>Stok Perlu Perhatian</h2>
                    <p>Produk dengan persediaan rendah</p>
                </div>
                <a href="#" class="sb-link">Lihat semua</a>
            </div>

            @if ($stokMinimal->count())
                <ul class="sb-list">
                    @foreach ($stokMinimal as $p)
                        <li>
                            <div class="sb-thumb">{!! $ikon['box'] !!}</div>
                            <div class="sb-nama">
                                <b title="{{ $p->nama }}">{{ $p->nama }}</b>
                                <small>{{ $p->sku }}</small>
                            </div>
                            <span class="sb-pill {{ $p->stok <= 0 ? 'habis' : '' }}">{{ $p->stok }} unit</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="sb-kosong">Semua stok masih aman.</div>
            @endif
        </section>
    </div>

    {{-- Keuangan periode --}}
    <div class="sb-uang">
        <div class="sb-card">
            <small>Biaya Diterima</small>
            <div class="sb-nilai">{{ $rp($biayaDiterima) }}</div>
        </div>
        <div class="sb-card">
            <small>Biaya Keluar</small>
            <div class="sb-nilai">{{ $rp($biayaKeluar) }}</div>
        </div>
        <div class="sb-card">
            <small>Margin</small>
            <div class="sb-nilai {{ $margin >= 0 ? 'plus' : 'minus' }}">{{ $margin < 0 ? '−' : '' }}{{ $rp($margin) }}</div>
        </div>
    </div>

    {{-- Kenaikan harga --}}
    <section class="sb-card sb-panel">
        <div class="sb-panel-head">
            <div>
                <h2>Laporan Kenaikan Harga</h2>
                <p>Harga beli naik dan menunggu konfirmasi</p>
            </div>
        </div>

        @if ($kenaikanHarga->count())
            <div class="sb-scroll">
                <table class="sb-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="r">Harga lama</th>
                            <th class="r">Harga beli</th>
                            <th class="r">Kenaikan</th>
                            <th class="r">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($kenaikanHarga as $k)
                        @php $persen = $k->harga_lama > 0 ? round($k->kenaikan_harga / $k->harga_lama * 100, 1) : null; @endphp
                        <tr>
                            <td><b>{{ $k->nama }}</b><br><small>{{ $k->nomor_sku }}</small></td>
                            <td class="r">{{ $rp($k->harga_lama) }}</td>
                            <td class="r">{{ $rp($k->harga_beli) }}</td>
                            <td class="r"><span class="sb-pill habis">+{{ $rp($k->kenaikan_harga) }}@if($persen !== null) · {{ number_format($persen, 1, ',', '.') }}%@endif</span></td>
                            <td class="r">{{ $angka($k->jumlah_barang) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="sb-kosong">Tidak ada kenaikan harga yang menunggu konfirmasi.</div>
        @endif
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0"></script>
<script>
    window.addEventListener('load', function () {
      var el = document.querySelector('#chart-transaksi');
      if (!el) return;
      try {
        if (typeof ApexCharts === 'undefined') {
            throw new Error('library ApexCharts tidak termuat (cek internet atau blokir CDN)');
        }
        new ApexCharts(el, {
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
        series: [
            { name: 'Masuk',  data: @json($grafik['masuk']) },
            { name: 'Keluar', data: @json($grafik['keluar']) },
        ],
        colors: ['#6c5ce7', '#ffa24c'],
        plotOptions: { bar: { columnWidth: '42%', borderRadius: 4, borderRadiusApplication: 'end' } },
        fill: {
            type: 'gradient',
            gradient: { shade: 'light', type: 'vertical', opacityFrom: 1, opacityTo: 0.75, stops: [0, 100] },
        },
        stroke: { show: true, width: 3, colors: ['transparent'] },
        dataLabels: { enabled: false },
        legend: { show: false },
        xaxis: {
            categories: @json($grafik['bulan']),
            labels: { style: { colors: '#8b90a3', fontSize: '11px' } },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { labels: { show: false } },
        grid: { borderColor: '#eceef4', strokeDashArray: 0, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
        tooltip: { y: { formatter: v => v + ' unit' } },
        }).render();
      } catch (e) {
        el.innerHTML = '<div class="sb-kosong">Grafik gagal ditampilkan: ' + e.message + '</div>';
        console.error(e);
      }
    });
</script>
@endsection
