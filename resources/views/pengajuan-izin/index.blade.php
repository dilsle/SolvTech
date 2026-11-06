@extends('layouts.kai')
@section('page_title', 'Data Pengajuan Izin Pegawai')

@section('content')
<div class="card py-4">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Card Statistik -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body text-center">
                        <p class="card-category text-muted">Menunggu Persetujuan</p>
                        <h4 class="card-title text-warning">{{ $jumlahMenunggu }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body text-center">
                        <p class="card-category text-muted">Disetujui</p>
                        <h4 class="card-title text-success">{{ $jumlahDisetujui }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body text-center">
                        <p class="card-category text-muted">Ditolak</p>
                        <h4 class="card-title text-danger">{{ $jumlahDitolak }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('pengajuan-izin.index') }}" class="row align-items-end mb-4">
            <div class="col-md-4 mb-2">
                <label class="form-label">Cari Nama / NIP</label>
                <input type="text" name="search" class="form-control" placeholder="Nama atau NIP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- Semua Status --</option>
                    <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
            </div>
            <div class="col-md-3 d-flex gap-1 mb-2">
                <button type="submit" class="btn btn-icon btn-round border w-50" title="Cari"><i class="fas fa-search"></i></button>
                <a href="{{ route('pengajuan-izin.index') }}" class="btn btn-outline-danger btn-round w-50 text-center pt-2">Reset</a>
            </div>
        </form>

        <!-- Tabel Pengajuan -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pegawai</th>
                        <th>Jenis</th>
                        <th>Tanggal Mulai - Selesai</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $index => $item)
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>{{ optional($item->pegawai)->nama }} ({{ optional($item->pegawai)->nip }})</td>
                        <td><span class="badge bg-secondary">{{ $item->jenis }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</td>
                        <td>{{ Str::limit($item->alasan, 30) }}</td>
                        <td>
                            <span class="badge bg-{{ $item->status == 'Disetujui' ? 'success' : ($item->status == 'Ditolak' ? 'danger' : 'warning') }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('pengajuan-izin.show', $item->id) }}" class="btn btn-sm btn-primary btn-round">
                                <i class="fa fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data pengajuan izin.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $pengajuans->links() }}
        </div>
    </div>
</div>
@endsection
