
<div>
    <!-- Button trigger modal -->
    <button type="button"
        class="btn btn-primary btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#formInputItem{{ $item->id }}">
        Laporkan
    </button>

    <!-- Modal -->
    <div class="modal fade"
        id="formInputItem{{ $item->id }}"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        tabindex="-1"
        aria-labelledby="labelInputItem{{ $item->id }}"
        aria-hidden="true">

        <div class="modal-dialog">
            <form class="modal-content"
                action="{{ route('stok-opname.input-data.update', $item->id) }}"
                method="POST">

                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title"
                        id="labelInputItem{{ $item->id }}">
                        Form Input Stok Opname
                    </h5>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="produk{{ $item->id }}" class="form-label">
                            Produk
                        </label>
                        <input type="text"
                            name="produk"
                            id="produk{{ $item->id }}"
                            class="form-control"
                            value="{{ $item->produk ?? '' }}"
                            readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label for="nomor_sku{{ $item->id }}" class="form-label">
                            Nomor SKU
                        </label>
                        <input type="text"
                            name="nomor_sku"
                            id="nomor_sku{{ $item->id }}"
                            class="form-control"
                            value="{{ $item->varian->nomor_sku ?? '' }}"
                            readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jumlah_stok{{ $item->id }}" class="form-label">
                            Jumlah Stok
                        </label>
                        <input type="number"
                            name="jumlah_stok"
                            id="jumlah_stok{{ $item->id }}"
                            class="form-control"
                            value="{{ $item->varian->stok_varian ?? 0 }}"
                            readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jumlah_dilaporkan{{ $item->id }}" class="form-label">
                            Jumlah Dilaporkan
                        </label>
                        <input type="number"
                            name="jumlah_dilaporkan"
                            id="jumlah_dilaporkan{{ $item->id }}"
                            class="form-control"
                            min="0"
                            required
                            value="{{ old('jumlah_dilaporkan', $item->jumlah_dilaporkan ?? '') }}">

                        @error('jumlah_dilaporkan')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="keterangan{{ $item->id }}" class="form-label">
                            Keterangan
                        </label>
                        <textarea name="keterangan"
                            id="keterangan{{ $item->id }}"
                            cols="30"
                            rows="5"
                            class="form-control">{{ old('keterangan', $item->keterangan ?? '') }}</textarea>

                        @error('keterangan')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Simpan Laporan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
