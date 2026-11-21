
<form class="d-flex align-items-end" action="/export-laporan-kartu-stok" method="POST">
    @csrf
    <input type="hidden" name="nomor_sku" value="{{ $nomorSku }}">

    <div class="form-group">
        <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
        <input type="date" name="tanggal_awal" id="tanggal_awal"
            class="form-control"
            value="{{ old('tanggal_awal') }}" required>

        @error('tanggal_awal')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
        <input type="date" name="tanggal_akhir" id="tanggal_akhir"
            class="form-control"
            value="{{ old('tanggal_akhir') }}" required>

        @error('tanggal_akhir')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="jenis_transaksi" class="form-label">Jenis Transaksi</label>
        <select name="jenis_transaksi" id="jenis_transaksi" class="form-control">
            <option value="">Pilih Jenis Transaksi</option>
            @foreach ($jenisTransaksi as $item)
                <option value="{{ $item }}"
                    {{ old('jenis_transaksi') == $item ? 'selected' : '' }}>
                    {{ $item }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group pt-3">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary">Export</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tanggalAwal = document.getElementById('tanggal_awal');
    const tanggalAkhir = document.getElementById('tanggal_akhir');

    function validasiTanggal() {
        tanggalAkhir.min = tanggalAwal.value;
    }

    tanggalAwal.addEventListener('change', validasiTanggal);

    tanggalAwal.addEventListener('invalid', function () {
        if (tanggalAwal.validity.valueMissing) {
            tanggalAwal.setCustomValidity('Tanggal awal wajib diisi.');
        }
    });

    tanggalAkhir.addEventListener('invalid', function () {
        if (tanggalAkhir.validity.valueMissing) {
            tanggalAkhir.setCustomValidity('Tanggal akhir wajib diisi.');
        } else if (tanggalAkhir.validity.rangeUnderflow) {
            tanggalAkhir.setCustomValidity(
                'Tanggal akhir harus sama dengan atau setelah tanggal awal.'
            );
        }
    });

    tanggalAwal.addEventListener('input', function () {
        tanggalAwal.setCustomValidity('');
    });

    tanggalAkhir.addEventListener('input', function () {
        tanggalAkhir.setCustomValidity('');
    });

    validasiTanggal();
});
</script>
