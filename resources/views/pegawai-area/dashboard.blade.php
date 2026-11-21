@extends('layouts.kai')
@section('page_title', 'Dashboard')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $warnaIzin = ['Menunggu' => 'warning', 'Disetujui' => 'success', 'Ditolak' => 'danger'];
@endphp

{{-- Sapaan --}}
<div class="card">
    <div class="card-body">
        <h4 class="fw-bold mb-1">Halo, {{ $pegawai->nama }}</h4>
        <p class="text-muted mb-3">
            {{ $pegawai->jabatan }} &middot;
            {{ now()->locale('id')->translatedFormat('l, d F Y') }}
        </p>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('transaksi-keluar.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Transaksi Baru
            </a>
            <a href="{{ route('master-data.stok-barang.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-boxes me-1"></i> Cek Stok
            </a>
        </div>
    </div>
</div>

{{-- Ringkasan penjualan --}}
<div class="row">
    @php
        $kartu = [
            ['label' => 'Transaksi Hari Ini', 'nilai' => number_format($penjualanHariIni->jumlah), 'ikon' => 'fa-receipt'],
            ['label' => 'Penjualan Hari Ini', 'nilai' => $rp($penjualanHariIni->total), 'ikon' => 'fa-money-bill-wave'],
            ['label' => 'Transaksi Bulan Ini', 'nilai' => number_format($penjualanBulanIni->jumlah), 'ikon' => 'fa-calendar-check'],
            ['label' => 'Penjualan Bulan Ini', 'nilai' => $rp($penjualanBulanIni->total), 'ikon' => 'fa-chart-line'],
        ];
    @endphp
    @foreach ($kartu as $k)
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 fs-3 text-primary"><i class="fas {{ $k['ikon'] }}"></i></div>
                    <div>
                        <small class="text-muted d-block">{{ $k['label'] }}</small>
                        <span class="fw-bold fs-5">{{ $k['nilai'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Grafik + stok menipis --}}
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Penjualan Saya, 7 Hari Terakhir</h4></div>
            <div class="card-body">
                <div style="height: 260px;"><canvas id="grafikPenjualan"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Stok Menipis</h4>
                <span class="badge bg-danger">{{ $stokMenipisTotal }}</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($stokMenipis as $v)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $v->produk->nama_produk ?? '-' }}</div>
                                <small class="text-muted">{{ $v->nama_varian }}</small>
                            </div>
                            <span class="badge bg-{{ $v->stok_varian == 0 ? 'danger' : 'warning' }}">
                                {{ $v->stok_varian }} pcs
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-4">Semua stok aman</li>
                    @endforelse
                </ul>
            </div>
            @if ($stokMenipisTotal > 5)
                <div class="card-footer text-center">
                    <a href="{{ route('master-data.stok-barang.index') }}">Lihat semua stok</a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Transaksi terakhir + izin --}}
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Transaksi Terakhir Saya</h4>
                <a href="{{ route('transaksi-keluar.index') }}">Lihat semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Pelanggan</th>
                                <th class="text-end">Total</th>
                                <th>Waktu</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transaksiTerakhir as $t)
                                <tr>
                                    <td>
                                        <a href="{{ route('transaksi-keluar.show', $t->nomor_transaksi) }}">
                                            {{ $t->nomor_transaksi }}
                                        </a>
                                    </td>
                                    <td>{{ $t->penerima ?: '-' }}</td>
                                    <td class="text-end">{{ $rp($t->total_harga) }}</td>
                                    <td>{{ $t->created_at->format('d/m H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('transaksi-keluar.struk', $t->nomor_transaksi) }}"
                                           target="_blank" class="btn btn-link p-0" title="Cetak struk">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada transaksi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-2">Pengajuan Izin Terakhir</p>
                @if ($izinTerakhir)
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">{{ $izinTerakhir->jenis }}</div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($izinTerakhir->tanggal_mulai)->format('d/m') }}
                                -
                                {{ \Carbon\Carbon::parse($izinTerakhir->tanggal_selesai)->format('d/m/Y') }}
                            </small>
                        </div>
                        <span class="badge bg-{{ $warnaIzin[$izinTerakhir->status] ?? 'secondary' }}">
                            {{ $izinTerakhir->status }}
                        </span>
                    </div>
                    @if ($izinTerakhir->status === 'Ditolak' && $izinTerakhir->catatan_admin)
                        <small class="text-danger d-block mt-2">{{ $izinTerakhir->catatan_admin }}</small>
                    @endif
                @else
                    <div class="text-muted">Belum ada pengajuan</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    new Chart(document.getElementById('grafikPenjualan'), {
        type: 'bar',
        data: {
            labels: @json($grafik['labels']),
            datasets: [{
                label: 'Penjualan (Rp)',
                data: @json($grafik['total']),
                backgroundColor: '#4a7bd0',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endpush
