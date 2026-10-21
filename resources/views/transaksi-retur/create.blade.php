@extends('layouts.kai')
@section('page_title',$pageTitle)
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-end">
        <button class="btn btn-primary" id="btn-submit-retur">Simpan Retur</button>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="nomor_transaksi" class="form-label">Nomor Transaksi</label>
            <select class="form-control border" id="select-transaksi"></select>
        </div>
        <div class="mt-5">
            <h5 id="nomor_transaksi"></h5>
            <p class="m-0" id="tanggal"></p>
            <p class="m-0" id="pengirim"></p>
            <p class="m-0" id="kontak"></p>
            <p class="m-0" id="jumlah_barang"></p>
            <p class="m-0" id="total_harga"></p>
        </div>
        <div class="my-3">
            <label class="form-label">Detail Barang</label>
            <table class="table" id="table-items">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Nomor Batch</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Sub Total</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('script')
    <script>
        $(document).ready(function () {
        let selectedItem = {}
        let returItems = []
        const numberFormat = new Intl.NumberFormat('id-ID')

        $('#select-transaksi').select2({
            placeholder: 'Pilih Transaksi',
            delay: 250,
            allowClear: true,
            theme: 'bootstrap-5',
            ajax: {
                url: ""
            }
        });
    });
    </script>
@endpush
