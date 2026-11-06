@extends('layouts.kai')
@section('page_title', 'Data Absensi Pegawai')

@section('content')
<div class="card py-4">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Card Statistik Ringkasan -->
        <div class="row mb-4">
            <div class="col-md-2 col-sm-4 mb-3">
                <div class="card card-stats card-round">
                    <div class="card-body text-center">
                        <p class="card-category text-muted">Pegawai Aktif</p>
                        <h4 class="card-title text-primary">{{ $totalPegawaiAktif }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 mb-3">
                <div class="card card-stats card-round">
                    <div class="card-body text-center">
                        <p class="card-category text-muted">Hadir</p>
                        <h4 class="card-title text-success">{{ $hadirHariIni }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 mb-3">
                <div class="card card-stats card-round">
                    <div class="card-body text-center">
                        <p class="card-category text-muted">Terlambat</p>
                        <h4 class="card-title text-warning">{{ $terlambatHariIni }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 mb-3">
                <div class="card card-stats card-round">
                    <div class="card-body text-center">
                        <p class="card-category text-muted">Izin</p>
                        <h4 class="card-title text-info">{{ $izinHariIni }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 mb-3">
                <div class="card card-stats card-round">
                    <div class="card-body text-center">
                        <p class="card-category text-muted">Sakit</p>
                        <h4 class="card-title text-secondary">{{ $sakitHariIni }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 mb-3">
                <div class="card card-stats card-round">
                    <div class="card-body text-center">
                        <p class="card-category text-muted">Belum Absen</p>
                        <h4 class="card-title text-danger">{{ $belumAbsen }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('absensi.index') }}" class="row align-items-end mb-4">
            <div class="col-md-3 mb-2">
                <label class="form-label">Cari Nama / NIP</label>
                <input type="text" name="search" class="form-control" placeholder="Nama atau NIP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Bulan</label>
                <input type="month" name="bulan" class="form-control" value="{{ request('bulan') }}">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- Semua Status --</option>
                    <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="Terlambat" {{ request('status') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                    <option value="Sakit" {{ request('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1 mb-2">
                <button type="submit" class="btn btn-icon btn-round border w-50" title="Cari"><i class="fas fa-search"></i></button>
                <a href="{{ route('absensi.index') }}" class="btn btn-outline-danger btn-round w-50 text-center pt-2">Reset</a>
            </div>
        </form>

        <!-- Tabel Absensi -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pegawai</th>
                        <th>NIP</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensis as $index => $abs)
                    <tr>
                        <td>{{ $absensis->firstItem() + $index }}</td>
                        <td>{{ optional($abs->pegawai)->nama ?? '-' }}</td>
                        <td>{{ optional($abs->pegawai)->nip ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($abs->tanggal)->format('d-m-Y') }}</td>
                        <td>{{ $abs->jam_masuk ?? '-' }}</td>
                        <td>{{ $abs->jam_keluar ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $abs->status == 'Hadir' ? 'success' : ($abs->status == 'Terlambat' ? 'warning' : ($abs->status == 'Izin' ? 'info' : 'secondary')) }}">
                                {{ $abs->status }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('absensi.show', $abs->id) }}" class="btn btn-sm btn-primary btn-round">
                                <i class="fa fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data absensi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $absensis->links() }}
        </div>
    </div>
</div>
@endsection
