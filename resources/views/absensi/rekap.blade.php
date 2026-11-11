@extends('layouts.kai')
@section('page_title', 'Rekap Absensi Pegawai')

@section('content')
<div class="card py-4">
    <div class="card-body">
        <!-- Filter Rekap -->
        <form method="GET" action="{{ route('absensi.rekap') }}" class="row align-items-end mb-4">
            <div class="col-md-3 mb-2">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-control">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->locale('id')->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-control">
                    @for($y=date('Y'); $y>=date('Y')-2; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Pegawai</label>
                <select name="pegawai_id" class="form-control">
                    <option value="">-- Semua Pegawai --</option>
                    @foreach($allPegawai as $p)
                        <option value="{{ $p->id }}" {{ $pegawaiId == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-1 mb-2">
                <button type="submit" class="btn btn-black btn-round w-50">Filter</button>
                <button type="button" onclick="window.print()" class="btn btn-primary btn-round w-50"><i class="fa fa-print"></i> Cetak</button>
            </div>
        </form>

        <div class="alert alert-info">
            Total Hari Kerja Operasional (Bulan ini): <strong>{{ $totalHariKerja }} Hari</strong>
        </div>

        <!-- Tabel Rekap -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama Pegawai</th>
                        <th>Hadir</th>
                        <th>Terlambat</th>
                        <th>Izin</th>
                        <th>Sakit</th>
                        <th>Tanpa Keterangan</th>
                        <th>Total Hari Kerja</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapData as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['pegawai']->nip }}</td>
                        <td>{{ $row['pegawai']->nama }}</td>
                        <td class="text-center text-success fw-bold">{{ $row['hadir'] }}</td>
                        <td class="text-center text-warning fw-bold">{{ $row['terlambat'] }}</td>
                        <td class="text-center text-info fw-bold">{{ $row['izin'] }}</td>
                        <td class="text-center text-secondary fw-bold">{{ $row['sakit'] }}</td>
                        <td class="text-center text-danger fw-bold">{{ $row['tanpa_keterangan'] }}</td>
                        <td class="text-center">{{ $row['total_hari_kerja'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data rekap absensi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
