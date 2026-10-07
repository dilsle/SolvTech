@props(['nomor_sku'])

<div>
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-default btn-kartu-stok text-primary" data-bs-toggle="modal" data-bs-target="#kartuStokModal" data-nomor-sku="{{ $nomor_sku }}">
    Kartu Stok
    </button>

    <!-- Modal -->
    <div class="modal fade" id="kartuStokModal" tabindex="-1" aria-labelledby="kartuStokModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="kartuStokModalLabel">Kartu Stok</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
            <div class="modal-body">
                <table class="table" id="table-kartu-stok">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nomor Transaksi</th>
                            <th>Note</th>
                            <th>Jumlah Keluar</th>
                            <th>Jumlah Masuk</th>
                            <th>Stok Akhir</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div id="pagination-kartu-stok" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        $(document).ready(function () {

            function loadKartuStok(nomorSku, pageUrl = null) {
                const url = pageUrl || `/kartu-stok/${nomorSku}`;
                const $tbody =$("#table-kartu-stok tbody");
                const $pagination = $("#pagination-kartu-stok");
                $tbody.html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');

                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (response) {
                        $tbody.empty();
                        console.log(response);
                        const logger = response.data;

                       if (logger.length === 0) {
                            $tbody.html('<tr><td colspan="8" class="text-center">Data Log Kosong</td></tr>');
                        }
                    }
                });
            }


            $(document).on("click", ".btn-kartu-stok", function () {
                let currentNomorSku = $(this).data('nomor-sku');

                loadKartuStok(currentNomorSku);
            });


        });
    </script>
@endpush
