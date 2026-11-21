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
                    <label for="note" class="form-label">Note</label>
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
            let returItems = [];
            const numberFormat = new Intl.NumberFormat('id-ID');

            $('#select-transaksi').select2({
                placeholder: 'Pilih Transaksi',
                delay: 250,
                allowClear: true,
                theme: 'bootstrap-5',
                ajax: {
                    url: "{{ route('get-data.transaksi-keluar') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { search: params.term };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(function (item) {
                                return {
                                    id: item.id,
                                    text: item.text + (item.sudah_diretur ? ' (sudah diretur)' : ''),
                                    disabled: item.sudah_diretur
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            function resetDetail() {
                returItems = [];
                renderTable();
                $("#nomor_transaksi, #tanggal, #pengirim, #kontak, #jumlah_barang, #total_harga").empty();
                $("#table-items tbody").empty();
                $("#select-transaksi-items").empty().append('<option></option>').trigger('change');
            }

            function getItemsTransaksi(nomor_transaksi) {
                $.ajax({
                    type: "GET",
                    url: `/get-data/transaksi-keluar/${nomor_transaksi}`,
                    success: function (response) {
                        resetDetail();

                        $("#nomor_transaksi").html(response.nomor_transaksi);
                        $("#tanggal").html(`Tanggal : ${response.tanggal}`);
                        $("#pengirim").html(`Penerima : ${response.penerima ?? '-'}`);
                        $("#kontak").html(`Kontak : ${response.kontak ?? '-'}`);
                        $("#jumlah_barang").html(`Jumlah Barang : ${response.items.reduce((t, i) => t + i.qty, 0)} pcs`);
                        $("#total_harga").html(`Total Harga : Rp. ${numberFormat.format(response.total_harga)}`);

                        $("#table-items tbody").append(
                            response.items.map(function (item, index) {
                                return `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.produk} ${item.varian ? item.varian.nama_varian : ''}</td>
                                        <td>${item.nomor_batch ?? '-'}</td>
                                        <td>${item.qty} pcs</td>
                                        <td> Rp. ${numberFormat.format(item.harga)}</td>
                                        <td> Rp. ${numberFormat.format(item.sub_total)}</td>
                                    </tr>
                                `;
                            })
                        );

                        const items = response.items.map(function (item) {
                            return {
                                id: item.id,
                                text: `${item.produk} ${item.varian ? item.varian.nama_varian : ''}`,
                                subTotal: item.harga * item.qty,
                                qty: item.qty,
                                harga: item.harga,
                                nomor_batch: item.nomor_batch,
                                nomor_sku: item.nomor_sku
                            };
                        });

                        $("#select-transaksi-items").empty().append('<option></option>').select2({
                            placeholder: "Pilih Produk",
                            allowClear: true,
                            theme: "bootstrap-5",
                            data: items
                        });
                    }
                });
            }

            $("#select-transaksi").on("select2:select", function (e) {
                getItemsTransaksi(e.params.data.text);
            });

            $("#select-transaksi").on("select2:clear", function () {
                resetDetail();
            });

            $("#btn-add").on("click", function (e) {
                e.preventDefault();

                const selectedItemData = $("#select-transaksi-items").select2("data")[0];
                const note = $("#note").val().trim();
                const qty = parseInt($("#qty").val());

                if (!selectedItemData || !selectedItemData.id || !note || isNaN(qty)) {
                    swal({ icon: 'warning', title: 'Perhatian', text: 'Input belum lengkap', timer: 3000 });
                    return;
                }

                if (qty <= 0) {
                    swal({ icon: 'warning', title: 'Perhatian', text: 'Qty tidak boleh kurang dari 1', timer: 3000 });
                    return;
                }

                const existingItem = returItems.find(item => item.id === selectedItemData.id);
                const qtySebelumnya = existingItem ? existingItem.qty : 0;

                if (qty + qtySebelumnya > Number(selectedItemData.qty)) {
                    swal({ icon: 'warning', title: 'Perhatian', text: 'Qty melebihi barang yang dibeli', timer: 3000 });
                    return;
                }

                const harga = Number(selectedItemData.harga);

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
                const tableBody = $("#table-retur tbody");
                tableBody.empty();

                let grandTotal = 0;

                returItems.forEach(function (item, index) {
                    grandTotal += item.subTotal;

                    tableBody.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.text}</td>
                            <td>${item.note}</td>
                            <td>${item.qty}</td>
                            <td>Rp. ${numberFormat.format(item.subTotal)}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm btn-hapus" data-index="${index}">Hapus</button>
                            </td>
                        </tr>
                    `);
                });

                if (returItems.length < 1) {
                    tableBody.append(`
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data yang akan diretur</td>
                        </tr>
                    `);
                }

                $("#grand-total").text("Rp. " + numberFormat.format(grandTotal));
            }

            $("#table-retur").on("click", ".btn-hapus", function () {
                returItems.splice(Number($(this).data("index")), 1);
                renderTable();
            });

            $("#btn-submit-retur").on("click", function () {
                const $btn = $(this);
                const teksAsli = $btn.text();
                const nomor_transaksi = $("#nomor_transaksi").text().trim();

                if (!nomor_transaksi) {
                    swal({ icon: "warning", title: "Perhatian", text: "Pilih transaksi terlebih dahulu" });
                    return;
                }

                if (returItems.length === 0) {
                    swal({ icon: "warning", title: "Perhatian", text: "Tidak ada data yang akan diretur" });
                    return;
                }

                $btn.prop('disabled', true).text('Menyimpan...');

                $.ajax({
                    type: "POST",
                    url: "{{ route('transaksi-retur.store') }}",
                    dataType: 'json',
                    data: {
                        _token: "{{ csrf_token() }}",
                        nomor_transaksi: nomor_transaksi,
                        items: returItems
                    },
                    success: function (response) {
                        if (response.success) {
                            swal({ icon: 'success', title: 'Berhasil', text: 'Transaksi retur berhasil disimpan', timer: 1500, buttons: false });
                            setTimeout(function () {
                                window.location.href = response.redirect_url;
                            }, 1500);
                        }
                    },
                    error: function (xhr) {
                        const pesan = (xhr.responseJSON && xhr.responseJSON.message)
                            || 'Terjadi kesalahan pada server. Coba lagi.';
                        swal({ icon: 'error', title: 'Gagal', text: pesan });
                        $btn.prop('disabled', false).text(teksAsli);
                    }
                });
            });

            renderTable();
        });
    </script>
@endpush
