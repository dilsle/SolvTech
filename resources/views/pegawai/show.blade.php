@extends('layouts.kai')
@section('page_title', 'Detail Pegawai')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-profile">
            <div class="card-header" style="background-image: url('{{ asset('template/assets/img/blogpost.jpg') }}')">
                <div class="profile-picture">
                    <div class="avatar avatar-xxl">
                        <span class="avatar-title rounded-circle border border-white bg-primary text-white fs-1">{{ substr($pegawai->nama, 0, 1) }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="user-profile text-center">
                    <div class="name">{{ $pegawai->nama }}</div>
                    <div class="job">{{ $pegawai->jabatan }}</div>
                    <div class="desc">NIP: {{ $pegawai->nip }}</div>
                    <div class="mt-2">
                        <span class="badge bg-{{ $pegawai->status == 'Aktif' ? 'success' : 'danger' }}">{{ $pegawai->status }}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row user-stats text-center">
                    <div class="col">
                        <a href="{{ route('pegawai.edit', $pegawai->id) }}" class="btn btn-primary btn-sm w-100"><i class="fa fa-edit"></i> Edit</a>
                    </div>
                    <div class="col">
                        <a href="{{ route('pegawai.index') }}" class="btn btn-secondary btn-sm w-100"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="card-title">Informasi Kontak & Akun</h4></div>
            <div class="card-body">
                <p><strong>No HP:</strong> {{ $pegawai->no_hp }}</p>
                <p><strong>Email Akun:</strong> {{ optional($pegawai->user)->email ?? '-' }}</p>
                <p><strong>Jenis Kelamin:</strong> {{ $pegawai->jenis_kelamin }}</p>
                <p><strong>Tanggal Masuk:</strong> {{ optional($pegawai->tanggal_masuk)->format('d M Y') }}</p>
                <p><strong>Alamat:</strong> {{ $pegawai->alamat ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Riwayat Absensi Terakhir -->
        <div class="card">
            <div class="card-header"><h4 class="card-title">Riwayat Absensi Terakhir</h4></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pegawai->absensis as $abs)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($abs->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $abs->jam_masuk ?? '-' }}</td>
                                <td>{{ $abs->jam_keluar ?? '-' }}</td>
                                <td><span class="badge bg-info">{{ $abs->status }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center">Belum ada riwayat absensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Riwayat Pengajuan Izin Terakhir -->
        <div class="card">
            <div class="card-header"><h4 class="card-title">Riwayat Pengajuan Izin Terakhir</h4></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Tanggal</th>
                                <th>Alasan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pegawai->pengajuanIzins as $izin)
                            <tr>
                                <td>{{ $izin->jenis }}</td>
                                <td>{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($izin->alasan, 30) }}</td>
                                <td>
                                    <span class="badge bg-{{ $izin->status == 'Disetujui' ? 'success' : ($izin->status == 'Ditolak' ? 'danger' : 'warning') }}">
                                        {{ $izin->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center">Belum ada riwayat pengajuan izin.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
