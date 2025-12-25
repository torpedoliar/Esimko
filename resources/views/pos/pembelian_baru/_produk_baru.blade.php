<form id="form_produk_baru">
    @csrf
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Kode</label>
                <input type="text" class="form-control" name="kode" id="kode_0" autocomplete="off" required >
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" class="form-control" name="nama_produk" autocomplete="off" required >
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Kelompok Barang</label>
                <select class="select2" style="width:100%" id="kelompok" name="kelompok"></select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Kategori</label>
                <select class="select2" style="width:100%" id="kategori" name="kategori"></select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Sub Kategori</label>
                <select class="select2" style="width:100%" id="sub_kategori" name="sub_kategori"></select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Stok Awal</label>
                <input type="text" class="form-control center" name="stok_awal" required >
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Stok Minimal</label>
                <input type="text" class="form-control center" name="stok_minimal" required >
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Satuan</label>
                <select class="select2" style="width:100%" name="satuan">
                    @foreach ($satuan as $key => $value)
                        <option value="{{$value->id}}">{{$value->satuan}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Harga Beli</label>
                <input type="text" class="form-control autonumeric" id="harga_0" data-a-dec="," data-a-sep="." name="harga_beli" onkeyup="hitung_harga_jual(0)" required >
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Harga Jual</label>
                <input type="text" class="form-control autonumeric" id="harga_jual_0" data-a-dec="," data-a-sep="." name="harga_jual" onkeyup="hitung_harga_jual(0)">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Margin Penjualan</label>
                <div style="display:flex">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                        <input type="text" class="form-control autonumeric" id="margin_nominal_0" name="margin_nominal" data-a-dec="," data-a-sep="." style="border-radius:0px" onkeyup="hitung_nominal(0)">
                    </div>
                    <div class="input-group" style="width:150px">
                        <input type="text" class="form-control" style="border-radius:0px;margin-left:-1px;text-align:center" id="margin_0" name="margin" required="" onkeyup="hitung_persen(0)">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <button class="btn btn-primary" type="submit">Simpan & Tambahkan</button>
</form>


<script>
    $('.select2').select2();

    let $form_produk_baru = $('#form_produk_baru');

    function get_kategori(select_target, parent_id, selected){
        $.get("{{ url('api/get_kategori') }}/"+parent_id, function (result) {
            $selectElement = $('#'+select_target);
            $selectElement.empty();
            $.each(result, function (i, value) {
                $selectElement.append('<option data-id="'+value.id+'" value="'+value.id+'">'+value.nama_kategori+'</option>');
            });
            if(selected !== ''){
                $selectElement.val(selected);
                $selectElement.select2();
            }
            $selectElement.trigger('change');
        });
    }

    get_kategori('kelompok','0', '');
    $('#kelompok').change(function () {
        let id = $(this).find('option:selected').attr('data-id');
        get_kategori('kategori',id, '');
    });
    $('#kategori').change(function () {
        let id = $(this).find('option:selected').attr('data-id');
        get_kategori('sub_kategori',id, '');
    });

    $form_produk_baru.on('keyup keypress', (e) => {
        let keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    $form_produk_baru.submit((e) => {
        e.preventDefault();
        let data = get_form_data($form_produk_baru);

        $.post("{{ url('manajemen_stok/pembelian_baru/item/produk_baru') }}", data, (result) => {
            console.log(result);
            $modal_tambah_produk.modal('toggle');
            $kode_produk.val(result.kode);
            $kode_produk.trigger('change');
        }).fail((xhr) => {
            console.log(xhr.responseText);
        });
    });
</script>
