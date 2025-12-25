<div class="card">
    <div class="card-header">
        <h5>{{ empty($akun) ? 'Tambah' : 'Ubah' }} Akun</h5>
    </div>
    <div class="card-body">
        <form action="{{ url('keuangan/akun' . (empty($akun) ? '' : ('/' . $akun->id))) }}" method="post" id="form_info">
            @csrf
            @if(!empty($akun))
                @method('put')
            @endif
            <input type="hidden" name="kode" value="{{ $kode }}" />
            <input type="hidden" name="parent_kode" value="{{ $parent_kode }}" />
            <input type="hidden" name="parent_id" value="{{ $parent_id }}" />
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kode_tampil">Kode Akun</label>
                        <input type="text" class="form-control" name="kode_tampil" id="kode_tampil" value="{{ $akun->kode_tampil ?? '' }}" />
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="tipe">Tipe Akun</label>
                        <select type="text" class="form-control select2" name="tipe" id="tipe">
                            @foreach($tipes as $tipe)
                                <option {{ $tipe == ($akun->tipe ?? '') ? 'selected' : '' }}>{{ $tipe }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="nama">Nama Akun</label>
                <input type="text" class="form-control" name="nama" id="nama" value="{{ $akun->nama ?? '' }}" />
            </div>
            <div class="d-flex flex-row justify-content-between" style="gap: 10px;">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn btn-secondary" type="button" onclick="discard()">Batal</button>
                <div class="d-flex flex-row justify-content-end" style="flex-grow: 1;gap: 10px">
                    @if(!empty($akun))
                        <button class="btn btn-info" type="button" onclick="info('', '{{ $akun->kode }}')">Tambah Sub</button>
                        <button class="btn btn-danger" type="button" onclick="delete_data({{ $akun->id }})">Delete</button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $('.select2').select2();
    $form_info = $('#form_info');
    $form_info.submit((e) => {
        e.preventDefault();
        let url = $form_info.attr('action');
        let data = new FormData($form_info.get(0));
        $.ajax({
            url, data,
            type: 'post',
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: () => discard(),
        }).fail((xhr) => {
            console.log(xhr.responseText);
        });
    });
</script>
