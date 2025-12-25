<div class="card">
    <div class="card-header">
        <h5>{{ empty($jurnal) ? 'Tambah' : 'Ubah' }} Akun</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-5">
                <form action="{{ url('keuangan/jurnal' . (empty($jurnal) ? '' : ('/' . $jurnal->id))) }}" method="post" id="form_info">
                    @csrf
                    @if(!empty($jurnal))
                        @method('put')
                    @endif
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="no_jurnal">No.Jurnal</label>
                                <input type="text" class="form-control" name="no_jurnal" id="no_jurnal" value="{{ $jurnal->no_jurnal ?? '' }}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal">Tanggal</label>
                                <input type="text" class="form-control datepicker" name="tanggal" id="tanggal" value="{{ format_date($jurnal->tanggal ?? '') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea type="text" class="form-control" name="keterangan" id="keterangan" rows="4">{!! $jurnal->keterangan ?? '' !!}</textarea>
                    </div>
                    <div class="d-flex flex-row justify-content-between" style="gap: 10px;">
                        <button class="btn btn-primary" type="submit">{{ empty($jurnal) ? 'Lanjutkan' : 'Simpan' }}</button>
                        <button class="btn btn-secondary" type="button" onclick="discard()">Batal</button>
                        <div class="d-flex flex-row justify-content-end" style="flex-grow: 1;gap: 10px">
                            @if(!empty($jurnal))
                                <button class="btn btn-danger" type="button" onclick="delete_data({{ $jurnal->id }})">Delete</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-7" id="jurnal_detail"></div>
        </div>
    </div>
</div>

<script>
    $('.datepicker').datepicker({
        autoclose: true,
        format: "dd-mm-yyyy",
    });
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
            success: (result) => {
                @empty($jurnal)
                    info(result.id);
                @else
                    discard();
                @endempty
            },
        }).fail((xhr) => {
            console.log(xhr.responseText);
        });
    });

    @if(!empty($jurnal))
    $jurnal_detail = $('#jurnal_detail');
    jurnal_detail = () => {
        $.get("{{ url('keuangan/jurnal/' . $jurnal->id . '/detail') }}", (result) => {
            $jurnal_detail.html(result);
        }).fail((xhr) => {
            $jurnal_detail.html(xhr.responseText);
        });
    }
    jurnal_detail();
    @endif
</script>
