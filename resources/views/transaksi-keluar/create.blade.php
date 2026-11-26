@extends('layouts.kai')
@section('page_title', $pageTitle)
@section('content')
<div class="card py-5">
    <div class="card-body">
        {{-- form --}}
        <form class="row col-12 justify-content-between" id="form-add-produk">
            <div class="alert alert-danger" id="alert-danger" style="box-shadow: none !important"></div>
            <div class="row">
                <div class="form-group w-25">
                    <label for="penerima" class="form-label">Penerima</label>
                    <input type="text" name="penerima" id="penerima" class="form-control">
                </div>
                <div class="form-group w-25">
                    <label for="kontak" class="form-label">Kontak</label>
                    <input type="text" name="kontak" id="kontak" class="form-control">
                </div>
                <div class="form-group mt-1">
                    <label for="keterangan" class="form-label">keterangan</label>
                    <textarea name="keterangan" id="keterangan" cols="30" rows="2" class="form-control"></textarea>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-4">
                    <label for="select-produk" class="form-label">Pilih Produk</label>
                    <select id="select-produk" class="form-control border py-3"></select>
                </div>
                <div class="col-2">
                    <label for="qty" class="form-label">Qty</label>
                    <input type="number" name="qty" id="qty" class="form-control">
                </div>
                <div class="col-2">
                    <label for="stok" class="form-label">Stok Tersedia</label>
                    <input type="number" name="stok" id="stok" class="form-control" readonly>
                </div>
                <div class="col-2">
                    <label for="harga" class="form-label">Harga</label>
                    <input type="number" name="harga" id="harga" class="form-control" readonly>
                </div>
                <div class="col-2">
                    <label for="btn-add" class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-dark btn-round w-100" id="btn-add">Tambahkan</button>
                </div>
            </div>
        </form>

        <table class="table mt-5" id="table-produk">
            <thead>
                <tr>
                    <th class="text-center" style="width: 15px">No</th>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Sub Total</th>
                    <th>Opsi</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Grand Total</th>
                    <th id="grand-total">0</th>
                </tr>
                <tr>
                    <th colspan="5" class="text-end align-middle">
                        Uang Dibayar
                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="btn-uang-pas">Uang Pas</button>
                    </th>
                    <th style="min-width: 180px">
                        <input type="text" inputmode="numeric" id="uang-dibayar" class="form-control" placeholder="0" autocomplete="off">
                    </th>
                </tr>
                <tr>
                    <th colspan="5" class="text-end">Kembalian</th>
                    <th id="kembalian">0</th>
                </tr>
                <tr>
                    <th colspan="6" class="text-end">
                        <button type="button" class="btn btn-primary" id="btn-simpan-transaksi">Simpan Transaksi</button>
                    </th>
                </tr>
            </tfoot>
        </table>
        {{-- table --}}
    </div>
</div>
@endsection
@push('script')
    <script>
    $(document).ready(function () {
        $("#alert-danger").hide();
        const numberFormat = new Intl.NumberFormat('id-ID');
        let selectedOption = {};
        let selectedProduk = [];

        function hitungTotal() {
            return selectedProduk.reduce((total, item) => total + item.subTotal, 0);
        }

        function ambilUangDibayar() {
            return parseInt(($("#uang-dibayar").val() || '').replace(/\D/g, '')) || 0;
        }

        function hitungKembalian() {
            const selisih = ambilUangDibayar() - hitungTotal();
            const $kembalian = $("#kembalian");

            if (selisih >= 0) {
                $kembalian.text(numberFormat.format(selisih)).removeClass('text-danger');
            } else {
                $kembalian.text('Kurang ' + numberFormat.format(-selisih)).addClass('text-danger');
            }
        }

        // Format ribuan otomatis saat mengetik uang dibayar
        $("#uang-dibayar").on("input", function () {
            const angka = $(this).val().replace(/\D/g, '');
            $(this).val(angka ? numberFormat.format(parseInt(angka)) : '');
            hitungKembalian();
        });

        $("#btn-uang-pas").on("click", function () {
            const total = hitungTotal();
            $("#uang-dibayar").val(total ? numberFormat.format(total) : '');
            hitungKembalian();
        });

        $('#select-produk').select2({
            placeholder: 'Pilih Produk',
            delay: 250,
            allowClear: true,
            theme: 'bootstrap-5',
            ajax: {
                url: "{{ route('get-data.varian-produk') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map((item) => {
                            return {
                                id: item.id,
                                text: item.text,
                                harga: item.harga,
                                stok: item.stok,
                                nomor_sku: item.nomor_sku
                            };
                        })
                    };
                }
            }
        });

        $('#select-produk').on('select2:select', function (e) {
            let data = e.params.data;
            $("#harga").val(data.harga);
            $("#stok").val(data.stok);
            selectedOption = data;
        });

        $("#form-add-produk").on("submit", function (e) {
            e.preventDefault();
            let qty = parseInt($("#qty").val());
            let harga = parseInt($("#harga").val());

            if (!selectedOption.id || !qty || !harga) {
                swal({ icon: 'warning', title: 'Perhatian', text: 'Input belum lengkap', timer: 3000 });
                return;
            }

            if (qty < 1 || harga < 1) {
                swal({ icon: 'warning', title: 'Perhatian', text: 'Qty atau Harga tidak boleh kurang dari 1', timer: 3000 });
                return;
            }

            let subTotal = qty * harga;
            let existingItem = selectedProduk.find(item => item.nomor_sku === selectedOption.nomor_sku);

            if (existingItem) {
                existingItem.qty = parseInt(existingItem.qty) + parseInt(qty);
                existingItem.harga = parseInt(harga);
                existingItem.subTotal = existingItem.qty * existingItem.harga;
            } else {
                selectedProduk.push({
                    text: selectedOption.text,
                    nomor_sku: selectedOption.nomor_sku,
                    qty: qty,
                    harga: harga,
                    subTotal: subTotal
                });
            }

            $("#select-produk").val(null).trigger('change');
            $("#qty").val('');
            $("#stok").val('');
            $("#harga").val('');
            renderTable();
        });

        $(document).on("click", ".btn-delete", function () {
            let nomorSku = $(this).data('nomor-sku');
            selectedProduk = selectedProduk.filter(item => item.nomor_sku !== nomorSku);
            renderTable();
        });

        function renderTable() {
            let tableBody = $("#table-produk tbody");
            tableBody.empty();

            selectedProduk.forEach((item, index) => {
                tableBody.append(`
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${item.text}</td>
                    <td>${item.qty}</td>
                    <td>${numberFormat.format(item.harga)}</td>
                    <td>${numberFormat.format(item.subTotal)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-round btn-delete btn-icon" data-nomor-sku="${item.nomor_sku}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                `);
            });

            if (selectedProduk.length === 0) {
                tableBody.append(`<tr><td colspan="6" class="text-center">Tidak ada data produk</td></tr>`);
            }

            $("#grand-total").text(numberFormat.format(hitungTotal()));
            hitungKembalian();
        }

        renderTable();

        $("#btn-simpan-transaksi").on("click", function (e) {
            e.preventDefault();
            $("#alert-danger").hide().empty();

            if (selectedProduk.length === 0) {
                swal({ icon: 'warning', title: 'Perhatian', text: 'Wajib menuliskan 1 produk yang akan dicatat', timer: 3000 });
                return;
            }

            const dibayar = ambilUangDibayar();

            if (dibayar < hitungTotal()) {
                swal({ icon: 'warning', title: 'Perhatian', text: 'Uang yang dibayarkan kurang dari total belanja', timer: 3000 });
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                method: "POST",
                url: "{{ route('transaksi-keluar.store') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    items: selectedProduk,
                    penerima: $("#penerima").val(),
                    kontak: $("#kontak").val(),
                    keterangan: $("#keterangan").val(),
                    uang_dibayar: dibayar,
                },
                success: function (response) {
                    if (response.success) {
                        swal({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Transaksi Keluar berhasil ditambahkan',
                            timer: 1500,
                            buttons: false
                        });
                        setTimeout(function () {
                            window.location.href = response.redirect_url + '?cetak=1';
                        }, 1500);
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).text('Simpan Transaksi');

                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        renderError(errors);
                    } else {
                        swal({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan pada server. Cek console untuk detail.',
                            timer: 3000
                        });
                        console.error(xhr.responseText);
                    }
                }
            });
        });

        function renderError(errors) {
            let alertBox = $("#alert-danger");
            alertBox.empty();
            Object.values(errors).forEach(err => {
                err.forEach(msg => {
                    alertBox.append(`<p class="mb-0">${msg}</p>`);
                });
            });
            alertBox.show();
        }
    });
    </script>
@endpush
