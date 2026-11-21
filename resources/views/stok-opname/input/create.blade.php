@extends('layouts.kai')
@section('page_title', $pageTitle)
@section('content')
    <div class="card py-5">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor SKU</th>
                        <th>Produk</th>
                        @if (auth()->user()->role === 'admin')
                            <th>Stok</th>
                        @endif
                        <th>Jumlah Terlapor</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Petugas</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1}}</td>
                        <td>{{ $item->varian->nomor_sku}}</td>
                        <td>{{ $item->produk}}</td>
                        @if (auth()->user()->role === 'admin')
                            <td>{{ $item->varian->stok_varian }}</td>
                        @endif
                        <td>{{ $item->jumlah_dilaporkan}}</td>
                        <td>
                            @if (auth()->user()->role === 'admin')
                                <span class="badge
                                    {{
                                        $item->status == 'selisih' ? 'bg-danger' :
                                        ($item->status == 'sesuai' ? 'bg-success' : 'bg-secondary')
                                    }}
                                text-capitalize">
                                    {{ $item->status }}
                                </span>
                            @else
                                <span class="badge {{ $item->status == 'belum dilaporkan' ? 'bg-secondary' : 'bg-primary' }}">
                                    {{ $item->status == 'belum dilaporkan' ? 'Belum dilaporkan' : 'Sudah dilaporkan' }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $item->keterangan}}</td>
                        <td>{{ $item->petugas}}</td>
                        <td>
                            @if ($item->status === 'belum dilaporkan' || auth()->user()->role === 'admin')
                                <x-stok-opname.form-input-item :item="$item"/>
                            @else
                                <span class="text-success small">
                                    <i class="fas fa-check-circle me-1"></i> Sudah dilaporkan
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tidak ada periode stok opname yang aktif</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
