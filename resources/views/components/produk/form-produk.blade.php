<div>
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-round {{ $id ? 'btn-primary btn-icon' : 'btn-dark' }}" data-bs-toggle="modal" data-bs-target="#formKategori{{ $id ?? '' }}">
        @if ($id)
            <i class="fas fa-edit"></i>
        @else
            <span>Produk Baru</span>
        @endif
    </button>

    <!-- Modal -->
    <div class="modal fade" id="formKategori{{ $id ?? '' }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ $action }}" method="POST">
                    @csrf
                    @if ($id)
                        @method('PUT')
                    @endif
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Form Produk</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="kategori_produk_id" class="form-label">Kategori Produk</label>
                            <select name="kategori_produk_id" id="kategori_produk_id" class="form-control">
                                <option value="">Pilih Kategori</option>
                                @foreach ($kategori as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('kategori_produk_id', $kategori_produk_id ?? '') == $item->id ? 'selected' : '' }}>
                                        {{ $item->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_produk_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="nama_produk" class="form-label">Nama Produk</label>
                            <input type="text" name="nama_produk" id="nama_produk" class="form-control" value="{{ old('nama_produk', $nama_produk ?? '') }}">
                            @error('nama_produk')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="deskripsi_produk" class="form-label">Deskripsi Produk</label>
                            <textarea name="deskripsi_produk" id="deskripsi_produk" rows="5" class="form-control">{{ old('deskripsi_produk', $deskripsi_produk ?? '') }}</textarea>
                            @error('deskripsi_produk')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if (!$id && ($errors->has('kategori_produk_id') || $errors->has('nama_produk') || $errors->has('deskripsi_produk')))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalElement = document.getElementById('formKategori');
                var modal = new bootstrap.Modal(modalElement);
                modal.show();
            });
        </script>
    @endif
</div>
