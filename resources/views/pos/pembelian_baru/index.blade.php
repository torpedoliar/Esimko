@php
    $app='manajemen_barang';
    $page='Pembelian';
    $subpage='Pembelian';
@endphp

@extends('layouts.kasir2')

@section('title')
    Pembelian |
@endsection

@section('content')

    <div class="container-fluid p-0 w-100 d-flex flex-row shadow-sm" style="height: calc(100vh - 155px)">
        <div class="w-75 bg-white" style="padding: 20px;">
            <div class="d-flex flex-row" style="gap: 10px;">
                <input type="text" id="kode_produk" name="kode_produk" class="form-control" placeholder="Kode Produk (F2)" autofocus>
                <div style="width: 350px;">
                    <select type="text" id="fid_supplier" name="fid_supplier" class="form-control select2">
                        <option value="">Pilih Supplier</option>
                        @foreach($supplier as $value)
                            <option value="{{ $value->id }}" {{ ($pembelian->fid_supplier ?? '') == $value->id ? 'selected' : '' }}>{{ $value->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-info text-nowrap px-4" type="button" onclick="open_modal_tambah_produk()">Tambah Barang (F3)</button>
                <button class="btn btn-primary text-nowrap px-4" type="button" onclick="open_modal_search_produk()">Cari Barang (F4)</button>
            </div>
            <div id="list_items" style="height: calc(100vh - 220px);overflow-y: scroll;"></div>
        </div>
        <div class="w-25 position-relative" style="background-color: #dcdde1;padding: 20px;">
            <div class="form-group">
                <h3><small>No. Pembelian</small><br><span id="no_pembelian">{{ !empty($pembelian) ? $pembelian->no_pembelian : '-' }}</span></h3>
            </div>
            <hr>
            <div class="form-group">
                <label>Diskon (F6)</label>
                <div style="display:flex">
                    <div class="input-group" style="width:150px">
                        <input type="text" name="diskon_persen" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="diskon_persen" value="{{ $pembelian->diskon_persen ?? '0' }}">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input type="text" name="diskon_nominal" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="diskon_nominal" value="{{ $pembelian->diskon_nominal ?? '0' }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>PPN</label>
                <div style="display:flex">
                    <div class="input-group" style="width:150px">
                        <input type="text" name="ppn_persen" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="ppn_persen" value="{{ $pembelian->ppn_persen ?? '0' }}">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input type="text" name="ppn_nominal" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="ppn_nominal" value="{{ $pembelian->ppn_nominal ?? '0' }}">
                    </div>
                </div>
            </div>
            <div class="form-group mb-3">
                <label>Biaya Tambahan</label>
                <input type="text" name="biaya_tambahan" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="biaya_tambahan" value="{{ $pembelian->biaya_tambahan ?? '0' }}">
            </div>
            <div class="form-group mb-0">
                <label>File Faktur</label>
                <input type="file" name="file_faktur" class="form-control" id="file_faktur">
                @if(($pembelian->file ?? '') != '')
                    <a href="{{ asset('storage/' . $pembelian->file) }}">Lihat File</a>
                @endif
            </div>
            <hr>
            <div class="form-group text-right">
                <h1><small>TOTAL</small><br><span id="total">0</span></h1>
            </div>
            <div class="position-absolute d-flex flex-column" style="bottom: 20px;width: calc(100% - 40px);gap: 10px;">
                <a href="{{ url('manajemen_stok/pembelian') }}" class="btn btn-secondary flex-grow-1" >Kembali</a>
                <button class="btn btn-danger flex-grow-1" type="button" onclick="delete_pembelian()">Batalkan (F7)</button>
                <button class="btn btn-success" type="button" onclick="selesai_pembelian()" id="button_bayar">SELESAI (F10)</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_search_produk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cari Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex flex-column" style="gap: 20px">
                    <input type="text" id="nama_produk" name="nama_produk" class="form-control form-control-lg" placeholder="Cari Nama Produk" onkeyup="search_produk()">
                    <div id="list_produk"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_tambah_produk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex flex-column" style="gap: 20px" id="modal_tambah_produk_body">

                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('assets/js/shortcut.js') }}"></script>
    <script>
        let _token = '{{ csrf_token() }}', sub_total = 0, no_pembelian = '', diskon = 0, ppn = 0, total = 0, tambahan = parseInt('{{ $pembelian->biaya_tambahan ?? '0' }}'),
            pembelian_id = '', produk_pertama = '';
        let $modal_search_produk = $('#modal_search_produk'),
            $modal_tambah_produk = $('#modal_tambah_produk'),
            $modal_tambah_produk_body = $('#modal_tambah_produk_body'),
            $fid_supplier = $('#fid_supplier'),
            $kode_produk = $('#kode_produk'),
            $list_items = $('#list_items'),
            $total = $('#total'),
            $list_produk = $('#list_produk'),
            $diskon_persen = $('#diskon_persen'),
            $diskon_nominal = $('#diskon_nominal'),
            $ppn_persen = $('#ppn_persen'),
            $ppn_nominal = $('#ppn_nominal'),
            $biaya_tambahan = $('#biaya_tambahan');
        let loading = false;



        let open_modal_search_produk = () => {
            search_produk();
            $modal_search_produk.modal('show');
            setTimeout(() => $('#nama_produk').focus(), 500);
        }

        let open_modal_tambah_produk = () => {
            $.get("{{ url('manajemen_stok/pembelian_baru/item/produk_create') }}", (result) => {
                $modal_tambah_produk_body.html(result);
                $modal_tambah_produk.modal('show');

                init_form_modal_tambah_produk();
            }).fail((xhr) => {
                console.log(xhr.responseText);
            });
        }

        let init_form_modal_tambah_produk = () => {


        }

        let pembelian_params = () => {
            return {
                _token,
                fid_supplier: $fid_supplier.find('option:selected').val(),
                total,
            }
        }

        let pembelian_baru = async () => {
            return new Promise(async (resolve, reject) => {
                var fileInput = document.getElementById("file_faktur").files[0];

                var params = new FormData();
                params.append("_token", _token);
                params.append("fid_supplier", $fid_supplier.find('option:selected').val());
                params.append("total", total);
                params.append("file_faktur", fileInput);

                try {
                    const data = await $.ajax({
                        url: "{{ url('manajemen_stok/pembelian_baru/create') }}",
                        type: "POST",
                        data: params,
                        processData: false,
                        contentType: false,
                    });
                    resolve(data); // Resolve the promise with the AJAX response
                } catch (xhr) {
                    console.log(xhr.responseText);
                    reject(xhr); // Reject the promise with the error
                }
            });
        };


        let search_items = () => {
            $.post("{{ url('manajemen_stok/pembelian_baru/item') }}/" + pembelian_id + '/search', {_token}, (result) => {
                $list_items.html(result);
            }).fail((xhr) => {
                $list_items.html(xhr.responseText);
            });
        }

        let update_item = (id) => {
            if (loading === false) {
                loading = true;

                // Create a FormData object
                var formData = new FormData();

                // Append the file input to the FormData
                var fileInput = document.getElementById("file_faktur").files[0];
                formData.append("file_faktur", fileInput);

                // Append the other parameters
                formData.append("_token", _token);
                formData.append("harga", $('#harga_' + id).val());
                formData.append("harga_jual", $('#harga_jual_' + id).val());
                formData.append("margin_nominal", $('#margin_nominal_' + id).val());
                formData.append("margin", $('#margin_' + id).val());
                formData.append("jumlah", $('#jumlah_' + id).val());

                $.ajax({
                    url: "{{ url('manajemen_stok/pembelian_baru/item') }}/" + id + '/update',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                })
                    .done(function(data) {
                        loading = false;
                        search_items();
                        // Handle success response here
                    })
                    .fail(function(xhr) {
                        loading = false;
                        console.log(xhr.responseText);
                    });
            }

        }

        let delete_pembelian = () => {
            if (loading === false) {
                loading = true;
                if (pembelian_id !== '') {
                    $.post("{{ url('manajemen_stok/pembelian_baru') }}/" + pembelian_id + '/delete', {_token}, () => {
                        loading = false;
                        window.location.href = '{{ url('manajemen_stok/pembelian_baru') }}';
                    });
                }
            }
        }

        let delete_item = (id) => {
            if (loading === false) {
                loading = true;
                $.post("{{ url('manajemen_stok/pembelian_baru/item') }}/" + id + '/delete', {_token}, () => {
                    loading = false;
                    search_items();
                }).fail((xhr) => {
                    loading = false;
                    console.log(xhr.responseText);
                });
            }
        }

        let search_produk = () => {
            let nama = $('#nama_produk').val();
            $.post("{{ url('manajemen_stok/pembelian_baru/cari_produk') }}", {_token, nama}, (result) => {
                $('#list_produk').html(result);
            }).fail((xhr) => {
                console.log(xhr.responseText);
            });
        }

        let pilih_produk = (kode) => {
            $kode_produk.val(kode);
            $kode_produk.focus();
            $kode_produk.trigger('change');
            $modal_search_produk.modal('toggle');
        }

        let hitung_total = () => {
            total = sub_total - diskon + ppn + tambahan;
            $total.html(add_commas(total));
        }

        let hitung_nominal = (item_id) => {
            let margin = $('#margin_nominal_' + item_id).val();
            if (margin === '') margin = 0;
            else margin = parseFloat(remove_commas(margin));
            let harga = $('#harga_' + item_id).val();
            harga = parseFloat(remove_commas(harga));

            let persen = (margin / harga) * 100;
            $('#margin_' + item_id).val(Math.round(persen));
            $('#harga_jual_' + item_id).val(add_commas(harga + margin));
        }

        let hitung_persen = (item_id) => {
            let persen = $('#margin_' + item_id).val();
            if (persen === '') persen = 0;
            else persen = parseFloat(persen.replace(',', '.'));

            let harga = $('#harga_' + item_id).val();
            harga = parseFloat(remove_commas(harga));
            let margin = (persen / 100) * harga;
            $('#margin_nominal_' + item_id).val(add_commas(margin));
            $('#harga_jual_' + item_id).val(add_commas(harga + margin));
        }

        let hitung_harga_jual = (item_id) => {
            let harga = $('#harga_' + item_id).val();
            if (harga === '') harga = 0;
            else harga = parseFloat(remove_commas(harga));

            let harga_jual = $('#harga_jual_' + item_id).val();
            if (harga_jual === '') harga_jual = 0;
            else harga_jual = parseFloat(remove_commas(harga_jual));

            if (harga > 0 && harga_jual > 0) {
                let margin = harga_jual - harga;
                let persen = (margin / harga) * 100;
                $('#margin_nominal_' + item_id).val(add_commas(margin));
                $('#margin_' + item_id).val(Math.round(persen));
            }
        }

        let selesai_pembelian = () => {
            if (loading === false) {
                let fid_supplier = $fid_supplier.find('option:selected').val();
                if (fid_supplier === '') {
                    swal.fire('Pilih supplier!');
                    return;
                }
                loading = true;

                // Create a FormData object
                var formData = new FormData();

                // Append the file input to the FormData
                var fileInput = document.getElementById("file_faktur").files[0];
                formData.append("file_faktur", fileInput);

                // Append the other parameters
                formData.append("_token", _token);
                formData.append("total", total);
                formData.append("diskon_persen", $diskon_persen.val());
                formData.append("diskon_nominal", $diskon_nominal.val());
                formData.append("ppn_persen", $ppn_persen.val());
                formData.append("ppn_nominal", $ppn_nominal.val());
                formData.append("biaya_tambahan", $biaya_tambahan.val());
                formData.append("fid_supplier", fid_supplier);

                $.ajax({
                    url: "{{ url('manajemen_stok/pembelian_baru') }}/" + pembelian_id + '/update',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                })
                    .done(function(data) {
                        loading = false;
                        window.location.href = '{{ url('manajemen_stok/pembelian_baru/selesai') }}?id=' + pembelian_id;
                        // Handle success response here
                    })
                    .fail(function(xhr) {
                        loading = false;
                        console.log(xhr.responseText);
                    });
            }
        };


        $fid_supplier.change(() => {
            if (loading === false) {
                let fid_supplier = $fid_supplier.find('option:selected').val();
                $.post("{{ url('manajemen_stok/pembelian_baru') }}/" + pembelian_id + '/update', {
                    _token,
                    fid_supplier
                });
            }
        });

        $kode_produk.change(async () => {
            if (loading === false) {
                loading = true;
                let is_baru = false;
                if (pembelian_id === '') {
                    let pembelian = await pembelian_baru();
                    no_pembelian = pembelian.no_pembelian;
                    pembelian_id = pembelian.id;
                    is_baru = true;
                }
                let kode = $kode_produk.val();
                $.post("{{ url('manajemen_stok/pembelian_baru/item/create') }}", {
                    _token, kode, jumlah: 1, diskon: 0, fid_pembelian: pembelian_id
                }, (result) => {
                    loading = false;
                    if (result.error) swal.fire(result.error);
                    else {
                        $kode_produk.val('');
                        if (is_baru === true) {
                            window.location.href = '{{ url('manajemen_stok/pembelian_baru') }}?no_pembelian=' + no_pembelian + '&id=' + pembelian_id;
                        } else search_items();

                    }
                }).fail((xhr) => {
                    loading = false;
                });
            }
        });

        $('#nama_produk').keypress((e) => {
            if (e.keyCode == 13) {
                $kode_produk.val(produk_pertama);
                $kode_produk.focus();
                $kode_produk.trigger('change');
                $modal_search_produk.modal('toggle');
            }
        });

        $diskon_persen.keyup(() => {
            let persen = $diskon_persen.val();
            if (persen === '') persen = 0;
            else persen = parseFloat(persen.replace(',', '.'));
            diskon = ((persen / 100) * sub_total).toFixed(0);
            $diskon_nominal.val(add_commas(diskon));

            hitung_total();
        });

        $diskon_nominal.keyup(() => {
            let nominal = $diskon_nominal.val();
            if (nominal === '') nominal = 0;
            else nominal = remove_commas(nominal);
            nominal = parseFloat(nominal);
            diskon = nominal;

            let persen = Math.round((diskon / sub_total) * 100);
            $diskon_persen.val(persen);

            hitung_total();
        });

        $ppn_persen.keyup(() => {
            let persen = $ppn_persen.val();
            if (persen === '') persen = 0;
            else persen = parseFloat(persen.replace(',', '.'));
            ppn = ((persen / 100) * sub_total).toFixed();
            $ppn_nominal.val(add_commas(ppn));

            hitung_total();
        });

        $ppn_nominal.keyup(() => {
            let nominal = $ppn_nominal.val();
            if (nominal === '') nominal = 0;
            else nominal = remove_commas(nominal);
            nominal = parseFloat(nominal);
            ppn = nominal;

            let persen = Math.round((ppn / sub_total) * 100);
            $ppn_persen.val(persen);

            hitung_total();
        });

        $biaya_tambahan.keyup(() => {
            let nominal = $biaya_tambahan.val();
            if (nominal === '') nominal = 0;
            else nominal = remove_commas(nominal);
            nominal = parseFloat(nominal);

            tambahan = nominal;
            hitung_total();
        });

        let get_form_data = ($form) => {
            let unindexed_array = $form.serializeArray();
            let indexed_array = {};
            $.map(unindexed_array, function(n, i){
                indexed_array[n['name']] = n['value'];
            });
            return indexed_array;
        }

        $modal_tambah_produk.on('hidden.bs.modal', () => {
            $form_produk_baru.find('input').val('');
        });

        shortcut.add("F2", () => $kode_produk.focus());
        shortcut.add("F3", () => open_modal_tambah_produk());
        shortcut.add("F4", () => open_modal_search_produk());
        shortcut.add("F6", () => $diskon_persen.focus());
        shortcut.add("F7", () => delete_pembelian());
        shortcut.add("F10", () => selesai_pembelian());

        @if(!empty($pembelian))
            pembelian_id = '{{ $pembelian->id }}';
            search_items();
        @endif

        $modal_search_produk.on('hidden.bs.modal', function () {
            $('#nama_produk').val('');
        });


        document.getElementById("file_faktur").addEventListener("change", function () {
            const fileInput = this;
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size; // in bytes
                const maxSize = 300 * 1024; // 100 KB

                if (fileSize > maxSize) {
                    alert("Ukuran file tidak boleh lebih dari 300 KB.");
                    fileInput.value = ""; // Clear the input
                }
            }
        });

    </script>
@endsection

