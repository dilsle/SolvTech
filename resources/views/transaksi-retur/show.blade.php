@extends('layouts.kai')
@section('page_title', $pageTitle)
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title">Nomor Retur : {{$transaksi->nomor_retur}} </h4>
            <a href="{{ route('transaksi-retur.index')}}" class="text-primary">Kembali</a>
        </div>
        <div class="card-body">
            <x-meta-item label="Pengirim" value="{{ $transaksi->transaksi->penerima}}"/>
            <x-meta-item label="Kontak" value="{{ $transaksi->transaksi->kontak}}"/>
            <x-meta-item label="Nomor Transaksi" value="{{ $transaksi->transaksi->nomor_transaksi}}"/>
            <x-meta-item label="Jumlah Harga" value=" Rp. {{number_format($transaksi->jumlah_harga)}}"/>
            <x-meta-item label="Jumlah Barang" value="{{number_format($transaksi->jumlah_barang)}} pcs"/>
            <x-meta-item label="Petugas" value=" {{$transaksi->petugas}}"/>
            <x-meta-item label="Tanggal Retur" value="{{$transaksi->tanggal_retur}}"/>


            <h4>Detail Barang</h4>

            <table class="table mt-4">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Nomor Batch</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Sub Total</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaksi->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->produk }} {{ $item->varian }}</td>
                            <td>{{ $item->nomor_batch }}</td>
                            <td>{{ number_format($item->qty) }} pcs</td>
                            <td>Rp. {{ number_format($item->harga) }}</td>
                            <td>Rp. {{ number_format($item->sub_total) }}</td>
                            <td>{{ $item->note }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                Tidak ada detail barang retur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6">Grand Total</th>
                        <th>Rp. {{ number_format($transaksi->jumlah_harga) }}</th>
                    </tr>
                </tfoot>
            </table>

        </div>
    </div>
@endsection
