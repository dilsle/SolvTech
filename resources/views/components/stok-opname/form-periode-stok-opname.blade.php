<div>
    <button type="button"
        class="btn btn-round {{ $id ? 'btn-primary btn-icon' : 'btn-dark' }}"
        data-bs-toggle="modal"
        data-bs-target="#formPeriode{{ $id ?? '' }}">
        @if ($id)
            <i class="fas fa-edit"></i>
        @else
            <span>Buat Periode</span>
        @endif
    </button>

    <div class="modal fade"
        id="formPeriode{{ $id ?? '' }}"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        tabindex="-1"
        aria-labelledby="formPeriodeLabel{{ $id ?? '' }}"
        aria-hidden="true">

        <div class="modal-dialog">
            <form class="modal-content form-ajax" action="{{ $action }}" method="POST" novalidate>
                @csrf
                @if ($id)
                    @method('PUT')
                @endif

                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="formPeriodeLabel{{ $id ?? '' }}">Form Periode Stok Opname</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger d-none" data-error-umum></div>

                    <div class="form-group">
                        <label for="tanggal_mulai{{ $id ?? '' }}" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai{{ $id ?? '' }}"
                            class="form-control" value="{{ $tanggal_mulai ?? '' }}">
                        <small class="text-danger" data-error="tanggal_mulai"></small>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_selesai{{ $id ?? '' }}" class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai{{ $id ?? '' }}"
                            class="form-control" value="{{ $tanggal_selesai ?? '' }}">
                        <small class="text-danger" data-error="tanggal_selesai"></small>
                    </div>

                    <div class="form-group d-flex flex-column">
                        <label for="is_active{{ $id ?? '' }}" class="form-label">
                            <input type="checkbox" name="is_active" id="is_active{{ $id ?? '' }}" value="1"
                                {{ ($is_active ?? 0) == 1 ? 'checked' : '' }}> Aktifkan Langsung
                        </label>
                        <small>
                            {{ $id ? 'Jika tidak diaktifkan maka tidak dapat melakukan pencatatan stok opname' : 'Pastikan Periode sebelumnya sudah selesai sebelum mengaktifkan periode ini' }}
                        </small>
                        <small class="text-danger" data-error="is_active"></small>
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
