@extends('layouts.kai')
@section('page_title', 'Data Pegawai')

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Statistik --}}
<div class="row">
    @php
        $stats = [
            ['label' => 'Total Pegawai',    'value' => $totalPegawai,    'icon' => 'fa-users',      'color' => 'primary'],
            ['label' => 'Pegawai Aktif',    'value' => $pegawaiAktif,    'icon' => 'fa-user-check', 'color' => 'success'],
            ['label' => 'Pegawai Nonaktif', 'value' => $pegawaiNonaktif, 'icon' => 'fa-user-times', 'color' => 'danger'],
        ];
    @endphp
    @foreach ($stats as $s)
        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-big text-center icon-{{ $s['color'] }} bubble-shadow-small me-3">
                        <i class="fas {{ $s['icon'] }}"></i>
                    </div>
                    <div>
                        <p class="card-category mb-0">{{ $s['label'] }}</p>
                        <h4 class="card-title mb-0">{{ $s['value'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Daftar Pegawai</h4>
    </div>

    <div class="card-body">
        {{-- Filter --}}
        <form action="{{ route('pegawai.index') }}" method="GET" class="row g-2 align-items-end mb-4">
            <div class="col-md-4">
                <label for="search" class="form-label">Cari Nama / NIP</label>
                <input type="text" name="search" id="search" class="form-control"
                    placeholder="Nama atau NIP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <select name="jabatan" id="jabatan" class="form-select">
                    <option value="">Semua Jabatan</option>
                    @foreach ($listJabatan as $jab)
                        <option value="{{ $jab }}" @selected(request('jabatan') == $jab)>{{ $jab }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Aktif" @selected(request('status') == 'Aktif')>Aktif</option>
                    <option value="Nonaktif" @selected(request('status') == 'Nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Cari</button>
                <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary" title="Reset">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>

        {{-- Tabel --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Pegawai</th>
                        <th>Jabatan</th>
                        <th>No HP</th>
                        <th>Tanggal Masuk</th>
                        <th>Status</th>
                        <th class="text-center" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pegawais as $index => $p)
                        <tr>
                            <td>{{ $pegawais->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold">{{ $p->nama }}</div>
                                <small class="text-muted">
                                    NIP {{ $p->nip }} &middot; {{ $p->jenis_kelamin == 'Laki-laki' ? 'Laki-laki' : 'Perempuan' }}
                                </small>
                            </td>
                            <td>{{ $p->jabatan }}</td>
                            <td>{{ $p->no_hp }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_masuk)->format('d-m-Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $p->status == 'Aktif' ? 'success' : 'danger' }}">{{ $p->status }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('pegawai.show', $p->id) }}" class="btn btn-link btn-info p-1" title="Detail">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('pegawai.edit', $p->id) }}" class="btn btn-link btn-info p-1" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                @if ($p->status == 'Aktif')
                                    <form action="{{ route('pegawai.destroy', $p->id) }}" method="POST" class="d-inline form-nonaktif">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-danger p-1" title="Nonaktifkan">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada data pegawai</td>
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

@push('script')
<script>
    document.querySelectorAll('.form-nonaktif').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            swal({
                title: 'Nonaktifkan pegawai?',
                text: 'Pegawai ini tidak akan bisa login lagi.',
                icon: 'warning',
                buttons: {
                    cancel: { text: 'Batal', visible: true },
                    confirm: { text: 'Ya, Nonaktifkan', className: 'btn btn-danger' }
                },
                dangerMode: true
            }).then(function (isConfirm) {
                if (isConfirm) form.submit();
            });
        });
    });
</script>
@endpush
