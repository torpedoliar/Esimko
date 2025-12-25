@php
    $app='pos';
    $page='Penjualan';
    $subpage='Penjualan';
@endphp

@extends('layouts.kasir2')

@section('title')
    Penjualan |
@endsection

@section('content')

    <div class="container-fluid p-0 w-100 d-flex flex-row shadow-sm" style="height: calc(100vh - 155px);overflow-y: scroll;">
        <div class="w-75 bg-white d-flex flex-column" style="padding: 20px;">
            <div class="d-flex flex-row" style="gap: 10px;">
                <input type="text" id="kode_produk" name="kode_produk" class="form-control form-control-lg" placeholder="Kode Produk (F2)" autofocus>
                <input type="text" id="no_anggota" name="no_anggota" class="form-control form-control-lg" placeholder="Kode Anggota (F3)" value="{{ !empty($penjualan->anggota) ? $penjualan->anggota->no_anggota : '' }}" style="width: 250px;" onchange="cari_anggota()">
                <button class="btn btn-primary text-nowrap px-4" type="button" onclick="open_modal_search_produk()">Cari Barang (F4)</button>
            </div>
            <div id="list_items" style="height: calc(100vh - 220px);overflow-y: scroll;"></div>
        </div>
        <div class="w-25 position-relative" style="background-color: #dcdde1;padding: 20px;">
            <div class="form-group">
                <h5><small>Nama Anggota</small><br><span id="nama_anggota">{{ !empty($penjualan->anggota) ? ($penjualan->anggota->nama_lengkap .' ('. $penjualan->anggota->no_anggota .')') : 'Bukan Anggota (0000)' }}</span></h5>
            </div>
            <hr>
            <div class="form-group">
                <h3><small>No. Transaksi</small><br><span id="no_transaksi">{{ !empty($penjualan) ? $penjualan->no_transaksi : '-' }}</span></h3>
            </div>
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select class="form-control select2" name="metode_pembayaran" id="metode_pembayaran">
                    @foreach ($metode_pembayaran as $key => $value)
                        <option value="{{ $value->id }}" >{{ $value->keterangan }}</option>
                    @endforeach
                </select>
            </div>
            <div id="voucher_belanja">
                <div class="form-group">
                    <label>Kode Voucher (F6)</label>
                    <input type="text" class="form-control" name="kode_voucher" >
                </div>
                <div class="form-group">
                    <label>Voucher Belanja</label>
                    <div class="row gutter-2">
                        <div class="col-md-6">
                            <select class="select2 form-control" id="tipe_voucher" name="tipe_voucher">
                                <option value="nominal">Nominal (Rp)</option>
                                <option value="persen">Persen (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group" id="voucher_nominal_group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" class="form-control autonumeric" id="voucher_nominal" name="voucher_nominal" data-a-dec="," data-a-sep="." >
                            </div>
                            <div class="input-group" id="voucher_persen_group" style="display: none;">
                                <input type="text" class="form-control" id="voucher_persen" name="voucher_persen" >
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group" id="group_limit" style="display: none;">
                <h5><small>Limit Pinjaman</small><br><span id="limit_anggota">0</span></h5>
            </div>
            <hr>
            <div class="form-group text-right">
                <h1><small>TOTAL</small><br><span id="total">0</span></h1>
            </div>
            <div class="position-absolute d-flex flex-column" style="bottom: 20px;width: calc(100% - 40px);gap: 10px;">
                <button class="btn btn-danger flex-grow-1" type="button" onclick="delete_penjualan()">Batalkan (F7)</button>
                <div class="d-flex flex-row justify-content-between" style="gap: 20px;">
                    <button class="btn btn-light flex-grow-1" type="button" onclick="open_modal_tunda()">List Tunda (F8)</button>
                    <button class="btn btn-warning flex-grow-1" type="button" onclick="tunda_penjualan()">Tunda (F9)</button>
                </div>
                <button class="btn btn-success" type="button" onclick="bayar()" id="button_bayar">BAYAR (F10)</button>
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
                <div class="modal-body d-flex flex-column" style="gap: 20px;">
                    <input type="text" id="nama_produk" name="nama_produk" class="form-control form-control-lg" placeholder="Cari Nama Produk" onkeyup="search_produk()">
                    <div id="list_produk"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_tunda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaksi Ditunda</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="list_penjualan_tunda">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_bayar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaksi Ditunda</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex flex-column" style="gap: 20px;">
                    <div class="text-right">
                        <h1>Total</h1>
                        <input type="text" class="form-control text-right" style="font-size: 30pt;" id="total2" readonly>
                    </div>
                    <div class="text-right">
                        <h1>Dibayar</h1>
                        <input type="text" class="form-control text-right autonumeric" data-a-dec="," data-a-sep="." style="font-size: 30pt;" id="dibayar" onkeyup="hitung_dibayar()">
                    </div>
                    <div class="text-right">
                        <h1>Kembali</h1>
                        <input type="text" class="form-control text-right" style="font-size: 30pt;" id="kembali" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/shortcut.js') }}"></script>
    <script>
        let _token = '{{ csrf_token() }}', total = 0, total_diskon = 0, tunai = 0, kembali = 0, no_transaksi = '',
            penjualan_id = '', limit = 0, tenor = 0, angsuran = 0, produk_pertama = '', boleh_simpan = true;
        let $modal_search_produk = $('#modal_search_produk'),
            $no_anggota = $('#no_anggota'),
            $kode_produk = $('#kode_produk'),
            $metode_pembayaran = $('#metode_pembayaran'),
            $kode_voucher = $('#kode_voucher'),
            $tipe_voucher = $('#tipe_voucher'),
            $voucher_belanja = $('#voucher_belanja'),
            $group_limit = $('#group_limit'),
            $tenor = $('#tenor'),
            $angsuran_perbulan = $('#angsuran_perbulan'),
            $voucher_nominal = $('#voucher_nominal'),
            $voucher_persen = $('#voucher_persen'),
            $list_items = $('#list_items'),
            $total = $('#total'),
            $total2 = $('#total2'),
            $dibayar = $('#dibayar'),
            $kembali = $('#kembali'),
            $list_produk = $('#list_produk'),
            $modal_tunda = $('#modal_tunda'),
            $modal_bayar = $('#modal_bayar');

        let open_modal_search_produk = () => {
            search_produk();
            $modal_search_produk.modal('show');
            setTimeout(() => $('#nama_produk').focus(), 500);
        }

        let penjualan_params = () => {
            return {
                _token,
                no_anggota: $no_anggota.val(),
                fid_metode_pembayaran: $metode_pembayaran.find('option:selected').val(),
                kode_voucher: $kode_voucher.val(),
                tipe_voucher: $tipe_voucher.find('option:selected').val(),
                voucher_nominal: $voucher_nominal.val(),
                voucher_persen: $voucher_persen.val(),
                total_pembayaran: total,
                diskon: total_diskon,
                tunai,
                kembali,
            }
        }

        let penjualan_baru = async () => {
            let params = penjualan_params();
            return await $.post("{{ url('pos/penjualan_baru/create') }}", params).fail((xhr) => {
                console.log(xhr.responseText);
            });
        }

        let search_items = () => {
            $.post("{{ url('pos/penjualan_baru/item') }}/" + penjualan_id + '/search', {_token}, (result) => {
                $list_items.html(result);
            }).fail((xhr) => {
                $list_items.html(xhr.responseText);
            });
        }

        let update_item = (id) => {
            $.post("{{ url('pos/penjualan_baru/item') }}/" + id + '/update', {
                _token,
                jumlah: $('#jumlah_' + id).val(),
                diskon: $('#diskon_' + id).val()
            }, (result) => {
                console.log(result);
                if (result.error) swal.fire(result.error);
                search_items();
            }).fail((xhr) => {
                console.log(xhr.responseText);
            });
        }

        let delete_penjualan = () => {
            if (penjualan_id !== '') {
                $.post("{{ url('pos/penjualan_baru') }}/" + penjualan_id + '/delete', {_token} ,() => {
                    window.location.href = '{{ url('pos/penjualan_baru') }}';
                });
            }
        }

        let tunda_penjualan = () => {
            window.location.href = '{{ url('pos/penjualan_baru') }}';
        }

        let open_modal_tunda = () => {
            $.post("{{ url('pos/penjualan_baru/list_tunda') }}", {
                _token
            }, (result) => {
                $('#list_penjualan_tunda').html(result);
                $modal_tunda.modal('show');
            }).fail((xhr) => {
                console.log(xhr.responseText);
            });
        }

        let resume_penjualan = (nomor) => {
            window.location.href = '{{ url('pos/penjualan_baru') }}?no_transaksi=' + nomor;
        }

        let cari_anggota = () => {
            let no_anggota = $('#no_anggota').val();
            $.post("{{ url('pos/penjualan_baru/cari_anggota') }}", {_token, no_anggota}, (result) => {
                if (result.error) swal.fire(result.error);
                else {
                    if (penjualan_id !== '') {
                        $.post("{{ url('pos/penjualan_baru') }}/" + penjualan_id + '/update', {
                            _token, no_anggota: result.no_anggota
                        }, (result) => {
                            console.log(result);
                        }).fail((xhr) => {
                            console.log(xhr.responseText);
                        });
                    }
                    $('#nama_anggota').html(result.nama_lengkap + '('+ result.no_anggota +')');
                }
            });
        }

        let delete_item = (id) => {
            $.post("{{ url('pos/penjualan_baru/item') }}/" + id + '/delete', {_token} ,() => {
                search_items();
            }).fail((xhr) => {
                console.log(xhr.responseText);
            });
        }

        let bayar = () => {
            let metode_pembayaran = $metode_pembayaran.find('option:selected').val();
            if (boleh_simpan === false) {
                swal.fire('anggota melebihi limit pinjaman !');
                return;
            }
            if (metode_pembayaran.toString() === '3') {
                if (total > limit) {
                    swal.fire('Total belanja melebihi limit pinjaman !');
                    return;
                }
                update_penjualan();
            } else {
                $modal_bayar.modal('show');
                setTimeout(() => $dibayar.focus(), 500);
            }
        }

        let hitung_dibayar = () => {
            let total = $('#total2').val(),
                dibayar = $('#dibayar').val();
            if (total === '') total = 0;
            else total = parseFloat(remove_commas(total));

            if (dibayar === '') dibayar = 0;
            else dibayar = parseFloat(remove_commas(dibayar));

            let kembali = dibayar - total;
            $('#kembali').val(add_commas(kembali));
        }

        let check_limit_anggota = () => {
            let anggota_id = $no_anggota.val();
            if (anggota_id === '' || anggota_id === '0000') {
                swal.fire('Kredit / Pinjaman hanya untuk anggota!');
                $metode_pembayaran.val(1).trigger('change');
            }else {
                $.get("{{ url('pos/penjualan/check_limit') }}?fid_anggota=" + anggota_id, (result) => {
                    limit = result;
                    $('#limit_anggota').html(add_commas(limit));
                    if (result < 0) {
                        swal.fire('Anggota sudah melebihi limit pinjaman');
                        boleh_simpan = false;
                    }
                });
            }
        }

        let update_penjualan = () => {
            $.post("{{ url('pos/penjualan_baru') }}/" + penjualan_id + '/update', {
                _token,
                total_pembayaran: total,
                tunai: remove_commas($dibayar.val()),
                kembali: kembali,
                fid_status: 2,
                diskon: total_diskon,
                fid_metode_pembayaran: $metode_pembayaran.find('option:selected').val(),
            },() => {
                window.location.href = '{{ url('pos/penjualan_baru') }}/' + penjualan_id + '/cetak_struk';
            });
        }

        let search_produk = () => {
            let nama = $('#nama_produk').val();
            $.post("{{ url('pos/penjualan_baru/cari_produk') }}", {_token, nama}, (result) => {
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

        let hapus_penjualan = (id) => {
            $.post("{{ url('pos/penjualan_baru') }}/" + id + '/delete', {_token}, () => {
                open_modal_tunda()
            });
        }

        $no_anggota.keydown((e) => {
            if (e.keyCode === 9) $metode_pembayaran.focus();
        });

        $kode_produk.change(async () => {
            let is_baru = false;
            if (penjualan_id === '') {
                let penjualan = await penjualan_baru();
                no_transaksi = penjualan.no_transaksi;
                penjualan_id = penjualan.id;
                is_baru = true;
            }
            let kode = $kode_produk.val();
            $.post("{{ url('pos/penjualan_baru/item/create') }}", {
                _token, kode, jumlah: 1, diskon: 0, fid_penjualan: penjualan_id
            }, (result) => {
                $kode_produk.val('');
                if (result.error) swal.fire(result.error);
                else {
                    if (is_baru === true) {
                        window.location.href = '{{ url('pos/penjualan_baru') }}?no_transaksi=' + no_transaksi;
                    } else search_items();

                }
            });
        });

        $dibayar.keypress((e) => {
            if (e.keyCode == 13) {
                kembali = $kembali.val();
                if (kembali === '') kembali = 0;
                else kembali = parseFloat(remove_commas(kembali));

                if (kembali < 0) swal.fire('Pembayaran kurang!');
                else update_penjualan();
            }
        });

        $metode_pembayaran.change(() => {
            let metode_pembayaran = $metode_pembayaran.find('option:selected').val();
            if (metode_pembayaran.toString() === '3') {
                $group_limit.show();
                $voucher_belanja.hide();
                check_limit_anggota();
            } else {
                $group_limit.hide();
                $voucher_belanja.show();
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

        shortcut.add("F2", () => $kode_produk.focus());
        shortcut.add("F3", () => $no_anggota.focus());
        shortcut.add("F4", () => open_modal_search_produk());
        shortcut.add("F6", () => $kode_voucher.focus());
        shortcut.add("F7", () => delete_penjualan());
        shortcut.add("F8", () => open_modal_tunda());
        shortcut.add("F9", () => tunda_penjualan());
        shortcut.add("F10", () => bayar());

        @if(!empty($penjualan))
            penjualan_id = '{{ $penjualan->id }}';
            $metode_pembayaran.val('{{ $penjualan->fid_metode_pembayaran }}').trigger('change');
            search_items();
        @endif

        $modal_search_produk.on('hidden.bs.modal', function () {
            $('#nama_produk').val('');
        });

    </script>
@endsection

