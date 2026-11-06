@extends('layouts.kai')
@section('page_title', 'Data Pegawai')

@section('content')
<div class="card py-5">
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
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-users"></i></div></div>
                            <div class="col col-stats ms-3"><p class="card-category">Total Pegawai</p><h4 class="card-title">{{ $totalPegawai }}</h4></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-user-check"></i></div></div>
                            <div class="col col-stats ms-3"><p class="card-category">Pegawai Aktif</p><h4 class="card-title">{{ $pegawaiAktif }}</h4></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-user-times"></i></div></div>
                            <div class="col col-stats ms-3"><p class="card-category">Pegawai Nonaktif</p><h4 class="card-title">{{ $pegawaiNonaktif }}</h4></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Tombol Tambah -->
        <div class="row align-items-end mb-4">
            <div class="col-11">
                <form class="row col-12 align-items-center" action="{{ route('pegawai.index')}}" method="GET">
                    <div class="col-4">
                        <label for="search" class="form-label">Cari Nama / NIP</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Nama atau NIP..." value="{{ request('search')}}">
                    </div>
                    <div class="col-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <select name="jabatan" id="jabatan" class="form-control">
                            <option value="">-- Semua Jabatan --</option>
                            @foreach($listJabatan as $jab)
                                <option value="{{ $jab }}" {{ request('jabatan') == $jab ? 'selected' : '' }}>{{ $jab }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Nonaktif" {{ request('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-2 d-flex gap-1">
                        <div>
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-icon btn-round border w-100" title="Cari">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div>
                            <label class="form-label">&nbsp;</label>
                            <button type="button" onclick="window.location.href='{{route('pegawai.index')}}'" class="btn btn-outline-danger btn-round w-100" title="Reset">
                                Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-1 text-end">
                <label class="form-label d-block">&nbsp;</label>
                <a href="{{ route('pegawai.create') }}" class="btn btn-primary btn-round" title="Tambah Pegawai">
                    <i class="fa fa-plus"></i>
                </a>
            </div>
        </div>

        <!-- Tabel Data Pegawai -->
        <div>
            <table class="table table-striped table-hover mt-3">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>L/P</th>
                        <th>Jabatan</th>
                        <th>No HP</th>
                        <th>Tanggal Masuk</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pegawais as $index => $p)
                        <tr>
                            <td>{{ $pegawais->firstItem() + $index }}</td>
                            <td>{{ $p->nip }}</td>
                            <td>{{ $p->nama }}</td>
                            <td>{{ $p->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}</td>
                            <td>{{ $p->jabatan }}</td>
                            <td>{{ $p->no_hp }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_masuk)->format('d-m-Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $p->status == 'Aktif' ? 'success' : 'danger' }}">{{ $p->status }}</span>
                            </td>
                            <td>
                                <div class="form-button-action">
                                    <a href="{{ route('pegawai.show', $p->id) }}" class="btn btn-link btn-info btn-lg" data-bs-toggle="tooltip" title="Detail">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('pegawai.edit', $p->id) }}" class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('pegawai.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menonaktifkan pegawai ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-danger btn-lg" data-bs-toggle="tooltip" title="Nonaktifkan">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data pegawai</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $pegawais->links() }}
        </div>
    </div>
</div>
@endsection
