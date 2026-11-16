@extends('layouts.kai')
@section('page_title', 'Data Absensi Pegawai')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Kehadiran Pegawai</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Manajemen Absensi</a></li>
        <li class="breadcrumb-item active">Detail Absensi</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-user-clock me-1"></i> Informasi Detail Absensi</span>
            <a href="{{ route('absensi.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Informasi Teks --}}
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 35%;">Nama Pegawai</th>
                            <td>{{ $absensi->pegawai->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('l, d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Jam Masuk</th>
                            <td>
                                <span class="badge bg-success">{{ $absensi->jam_masuk ?? 'Belum Absen' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Jam Keluar</th>
                            <td>
                                <span class="badge bg-danger">{{ $absensi->jam_keluar ?? 'Belum Absen' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status Kehadiran</th>
                            <td>
                                @if($absensi->status == 'Hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($absensi->status == 'Terlambat')
                                    <span class="badge bg-warning text-dark">Terlambat</span>
                                @else
                                    <span class="badge bg-secondary">{{ $absensi->status }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- Informasi Foto Dokumentasi --}}
                <div class="col-md-6">
                    <div class="row text-center">
                        {{-- Foto Masuk --}}
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">Foto Masuk</h6>
                            @if($absensi->foto_masuk)
                                <a href="{{ asset('storage/' . $absensi->foto_masuk) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $absensi->foto_masuk) }}" alt="Foto Masuk" class="img-thumbnail rounded shadow-sm" style="max-height: 220px; object-fit: cover;">
                                </a>
                                <small class="d-block text-muted mt-1">Klik gambar untuk memperbesar</small>
                            @else
                                <div class="alert alert-secondary py-4">Tidak ada foto masuk</div>
                            @endif
                        </div>

                        {{-- Foto Keluar --}}
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">Foto Keluar</h6>
                            @if($absensi->foto_keluar)
                                <a href="{{ asset('storage/' . $absensi->foto_keluar) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $absensi->foto_keluar) }}" alt="Foto Keluar" class="img-thumbnail rounded shadow-sm" style="max-height: 220px; object-fit: cover;">
                                </a>
                                <small class="d-block text-muted mt-1">Klik gambar untuk memperbesar</small>
                            @else
                                <div class="alert alert-secondary py-4">Belum absen keluar</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
