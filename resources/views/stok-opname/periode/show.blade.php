
@extends('layouts.kai')

@section('page_title', $pageTitle)

@section('content')
    <div class="card py-5">
        <div class="card-body">
            <div class="mb-4">
                <x-meta-item label="Periode" value="{{ $dataPeriode->periode }}" />
                <x-meta-item label="Jumlah Barang" value="{{ $dataPeriode->jumlah_barang }}" />
                <x-meta-item label="Jumlah Barang Sesuai" value="{{ $dataPeriode->jumlah_barang_sesuai }}" />
                <x-meta-item label="Jumlah Barang Selisih" value="{{ $dataPeriode->jumlah_barang_selisih }}" />
                <x-meta-item label="Status Kerja" value="{{ $dataPeriode->is_active ? 'Aktif' : 'Tidak Aktif' }}" />
                <x-meta-item
                    label="Pelaporan Stok Opname"
                    value="{{ $dataPeriode->is_completed ? 'Lengkap' : 'Belum Lengkap' }}"
                />
            </div>

            <div class="mt-5">
                <div class="mb-3">
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        id="btn-update-produk">
                        Update Daftar Produk
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor SKU</th>
                                <th>Produk</th>
                                <th>Stok</th>
                                <th>Jumlah Terlapor</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th>Petugas</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($dataPeriode->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->nomor_sku }}</td>
                                    <td>{{ $item->produk }}</td>
                                    <td>{{ $item->jumlah_stok }}</td>
                                    <td>{{ $item->jumlah_dilaporkan ?? '-' }}</td>

                                    <td>
                                        <span class="badge text-capitalize
                                            {{ $item->status == 'selisih'
                                                ? 'bg-danger'
                                                : ($item->status == 'sesuai'
                                                    ? 'bg-success'
                                                    : 'bg-secondary') }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>

                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                    <td>{{ $item->petugas ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        Belum ada data stok opname.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function () {
            $('#btn-update-produk').on('click', function () {
                const button = $(this);

                button.prop('disabled', true);

                $.ajax({
                    type: 'POST',
                    url: "{{ route('stok-opname.update-produk') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        periode_id: "{{ $dataPeriode->id }}"
                    },
                    success: function (response) {
                        const isSuccess = response.success;

                        swal({
                            icon: isSuccess ? 'success' : 'warning',
                            title: isSuccess ? 'Berhasil' : 'Gagal',
                            text: response.message,
                            timer: 3000
                        }).then(() => {
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            }
                        });
                    },
                    error: function (xhr) {
                        swal({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message
                                || 'Terjadi kesalahan saat memperbarui daftar produk.'
                        });
                    },
                    complete: function () {
                        button.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
