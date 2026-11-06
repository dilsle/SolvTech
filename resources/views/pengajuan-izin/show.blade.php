@extends('layouts.kai')
@section('page_title', 'Detail Pengajuan Izin')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Informasi Pengajuan</h4></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 35%">Nama Pegawai</th>
                        <td>: {{ optional($pengajuan->pegawai)->nama }}</td>
                    </tr>
                    <tr>
                        <th>NIP</th>
                        <td>: {{ optional($pengajuan->pegawai)->nip }}</td>
                    </tr>
                    <tr>
                        <th>Jenis Pengajuan</th>
                        <td>: <span class="badge bg-secondary">{{ $pengajuan->jenis }}</span></td>
                    </tr>
                    <tr>
                        <th>Periode Tanggal</th>
                        <td>: {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>Alasan</th>
                        <td>: {{ $pengajuan->alasan }}</td>
                    </tr>
                    <tr>
                        <th>Status Saat Ini</th>
                        <td>:
                            <span class="badge bg-{{ $pengajuan->status == 'Disetujui' ? 'success' : ($pengajuan->status == 'Ditolak' ? 'danger' : 'warning') }}">
                                {{ $pengajuan->status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Diproses Oleh</th>
                        <td>: {{ optional($pengajuan->admin)->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Catatan Admin</th>
                        <td>: {{ $pengajuan->catatan_admin ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="card-title">Bukti Pendukung / Surat</h4></div>
            <div class="card-body text-center">
                @if($pengajuan->bukti)
                    <img src="{{ asset('storage/' . $pengajuan->bukti) }}" class="img-fluid rounded border" alt="Bukti Surat" style="max-height: 300px;">
                @else
                    <span class="text-muted">Tidak ada lampiran bukti.</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        @if($pengajuan->status == 'Menunggu')
        <div class="card">
            <div class="card-header"><h4 class="card-title">Proses Pengajuan Izin</h4></div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <!-- Form Setujui -->
                <form action="{{ route('pengajuan-izin.approve', $pengajuan->id) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Catatan Admin (Opsional)</label>
                        <input type="text" name="catatan_admin" class="form-control" placeholder="Catatan persetujuan...">
                    </div>
                    <button type="submit" class="btn btn-success btn-round w-100" onclick="return confirm('Setujui pengajuan izin ini?')">
                        <i class="fa fa-check"></i> Setujui Pengajuan
                    </button>
                </form>

                <hr>

                <!-- Form Tolak -->
                <form action="{{ route('pengajuan-izin.reject', $pengajuan->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_admin" class="form-control @error('catatan_admin') is-invalid @enderror" rows="3" required placeholder="Wajib diisi jika menolak pengajuan..."></textarea>
                        @error('catatan_admin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-danger btn-round w-100" onclick="return confirm('Tolak pengajuan izin ini?')">
                        <i class="fa fa-times"></i> Tolak Pengajuan
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card bg-light">
            <div class="card-body text-center py-5">
                <h5 class="text-muted">Pengajuan ini telah selesai diproses dengan status: <strong>{{ $pengajuan->status }}</strong></h5>
            </div>
        </div>
        @endif

        <div class="text-end mt-3">
            <a href="{{ route('pengajuan-izin.index') }}" class="btn btn-secondary btn-round">Kembali ke Data Pengajuan</a>
        </div>
    </div>
</div>
@endsection
