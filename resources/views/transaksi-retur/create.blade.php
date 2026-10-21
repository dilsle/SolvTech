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

        <div class="row mt-5">
            <div class="col-4">
                <div>
                    <label for="produk" class="form-label">Pilih Produk</label>
                    <select class="form-control" id="select-transaksi-items">
                        <option value="" selected>Pilih Produk</option>
                    </select>
                </div>
                <div class="mt-2">
                    <label for="note" class="formlabel">Note</label>
                    <textarea class="form-control" id="note" cols="30" rows="5"></textarea>
                </div>
                <div class="mt-2">
                    <label for="qty" class="form-label">Qty</label>
                    <input type="number" class="form-control" id="qty">
                </div>
                <div class="mt-2">
                    <button class="btn btn-dark w-100" id="btn-add">Tambahkan</button>
                </div>
            </div>
            <div class="col-8">
                <label for="" class="form-label">Daftar Barang Siap Retur</label>
                <table class="table" id="table-retur">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Note</th>
                            <th>Qty</th>
                            <th>Total Harga</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Grand Total</th>
                            <th id="grand-total">0</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
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
                url: "{{ route('get-data.transaksi-keluar') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    let query = {
                        search: params.term
                    }
                    return query
                },
                processResults: function(data) {
                    return {
                        results:data.map((item)=>{
                            return {
                                id: item.id,
                                text: item.text
                            }
                        })
                    }
                },
                cache:true
            }
        })

        function getItemsTransaksi(nomor_transaksi) {

            $.ajax({
                type: "GET",
                url: `/get-data/transaksi-keluar/${nomor_transaksi}`,
                success: function (response) {
                    console.log(response);
                    $("#nomor_transaksi").html(response.nomor_transaksi);
                    $("#tanggal").html(`Tanggal : ${response.tanggal}`);
                    $("#pengirim").html(`Pengirim : ${response.penerima}`);
                    $("#kontak").html(`Kontak : ${response.kontak}`);
                    $("#jumlah_barang").html(`Jumlah Barang : ${response.jumlah_barang} pcs`);
                    $("#total_harga").html(`Total Harga : Rp. ${numberFormat.format(response.total_harga)}`);

                    $("#table-items tbody").append(
                        response.items.map((item, index)=>{
                            return `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.produk} ${item.varian.nama_varian}</td>
                                    <td>${item.nomor_batch}</td>
                                    <td>${item.qty} pcs</td>
                                    <td> Rp. ${numberFormat.format(item.harga)}</td>
                                    <td> Rp. ${numberFormat.format(item.sub_total)}</td>
                                </tr>
                            `
                        })
                    )

                    let items = response.items.map((item)=>{
                        return {
                            id: item.id,
                            text: `${item.produk} ${item.varian.nama_varian}`,
                            subTotal: item.harga * item.qty,
                            qty: item.qty,
                            harga: item.harga,
                            nomor_batch: item.nomor_batch,
                            nomor_sku: item.nomor_sku
                        }
                    })

                    $("#select-transaksi-items").select2({
                        placeholder: "Pilih Produk",
                        allowClear: true,
                        theme: "bootstrap-5",
                        data:items
                    })
                }
            });
        }

        $("#select-transaksi").on("select2:select", function (e) {
            selectedItem = e.params.data;
            getItemsTransaksi(selectedItem.text);
        })
        $("#select-transaksi-items").on("select2:select", function (e) {
            selectedItem = e.params.data;
        })

        $("#btn-add").on("click", function (e) {
            e.preventDefault();

            let selectedItemData = $("#select-transaksi-items").select2("data")[0];
            let note = $("#note").val().trim();
            let qty = parseInt($("#qty").val());

            if (!selectedItemData || !note || !$("#qty").val() || isNaN(qty)) {
                swal({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Input belum lengkap',
                    timer: 3000
                });
                return;
            }

            if (qty <= 0) {
                swal({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Qty tidak boleh kurang dari 1',
                    timer: 3000
                });
                return;
            }

            // Cari barang retur berdasarkan ID detail transaksi
            let existingItem = returItems.find(
                item => item.id === selectedItemData.id
            );

            // Hitung qty yang sudah dimasukkan untuk barang ini
            let qtySebelumnya = existingItem ? existingItem.qty : 0;

            if (qty + qtySebelumnya > Number(selectedItemData.qty)) {
                swal({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Qty melebihi barang yang dikirim',
                    timer: 3000
                });
                return;
            }

            let harga = Number(selectedItemData.harga);

            if (existingItem) {
                existingItem.qty += qty;
                existingItem.subTotal += harga * qty;
                existingItem.note = existingItem.note + "; " + note;
            } else {
                returItems.push({
                    id: selectedItemData.id,
                    text: selectedItemData.text,
                    note: note,
                    qty: qty,
                    harga: harga,
                    subTotal: harga * qty,
                    nomor_batch: selectedItemData.nomor_batch,
                    nomor_sku: selectedItemData.nomor_sku
                });
            }

            renderTable();

            $("#select-transaksi-items").val(null).trigger("change");
            $("#note").val("");
            $("#qty").val("");
        });



        function renderTable() {
            let tableBody = $("#table-retur tbody");
            tableBody.empty();

            let grandTotal = 0;

            returItems.forEach((item, index) => {
                grandTotal += item.subTotal;

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.text}</td>
                        <td>${item.note}</td>
                        <td>${item.qty}</td>
                        <td>Rp. ${numberFormat.format(item.subTotal)}</td>
                        <td>
                            <button type="button"
                                class="btn btn-danger btn-sm btn-hapus"
                                data-index="${index}">
                                Hapus
                            </button>
                        </td>
                    </tr>
                `;

                tableBody.append(row);
            });

            if (returItems.length < 1) {
                tableBody.append(`
                    <tr>
                        <td colspan="6" class="text-center">
                            Tidak ada data yang akan diretur
                        </td>
                    </tr>
                `);
            }

            $("#grand-total").text(
                "Rp. " + numberFormat.format(grandTotal)
            );
        }

        $("#table-retur").on("click", ".btn-hapus", function () {
            let index = Number($(this).data("index"));

            returItems.splice(index, 1);
            renderTable();
        });



    });
    </script>
@endpush
