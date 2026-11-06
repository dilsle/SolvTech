@extends('layouts.kai')
@section('page_title', 'Pengaturan Absensi & Operasional Bengkel')

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Konfigurasi Jam & Lokasi GPS</h4></div>
            <form action="{{ route('pengaturan-absensi.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Jam Masuk</label>
                            <input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk', $pengaturan->jam_masuk) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Batas Toleransi</label>
                            <input type="time" name="batas_toleransi" class="form-control" value="{{ old('batas_toleransi', $pengaturan->batas_toleransi) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jam Pulang</label>
                            <input type="time" name="jam_pulang" class="form-control" value="{{ old('jam_pulang', $pengaturan->jam_pulang) }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude Bengkel</label>
                            <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $pengaturan->latitude) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude Bengkel</label>
                            <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $pengaturan->longitude) }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Radius Maksimal (Meter)</label>
                            <input type="number" name="radius_meter" class="form-control" value="{{ old('radius_meter', $pengaturan->radius_meter) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Operasional Hari Minggu</label>
                            <select name="buka_minggu" class="form-control" required>
                                <option value="0" {{ $pengaturan->buka_minggu == 0 ? 'selected' : '' }}>Tutup (Libur)</option>
                                <option value="1" {{ $pengaturan->buka_minggu == 1 ? 'selected' : '' }}>Buka (Hari Kerja)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-action text-end">
                    <button type="submit" class="btn btn-primary btn-round">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Hari Libur Khusus</h4></div>
            <div class="card-body">
                <form action="{{ route('pengaturan-absensi.store-libur') ?? '#' }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Tanggal Libur</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan Libur</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Hari Raya / Libur Nasional" required>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-round w-100">Tambah Hari Libur</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hariLiburs ?? [] as $libur)
                            <tr>
                                <td>{{ $libur->tanggal->format('d-m-Y') }}</td>
                                <td>{{ $libur->keterangan }}</td>
                                <td>
                                    <form action="{{ route('pengaturan-absensi.destroy-libur', $libur->id) ?? '#' }}" method="POST" onsubmit="return confirm('Hapus hari libur ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center">Belum ada hari libur khusus.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
