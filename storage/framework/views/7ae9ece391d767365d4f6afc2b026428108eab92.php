<?php
    $app='pos';
    $page='Penjualan';
    $subpage='Penjualan';
?>



<?php $__env->startSection('title'); ?>
    Penjualan |
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <div class="container-fluid p-0 w-100 d-flex flex-row shadow-sm" style="height: calc(100vh - 155px);overflow-y: scroll;">
        <div class="w-75 bg-white d-flex flex-column" style="padding: 20px;">
            <div class="d-flex flex-row" style="gap: 10px;">
                <input type="text" id="kode_produk" name="kode_produk" class="form-control form-control-lg" placeholder="Kode Produk (F2)" autofocus>
                <div class="input-group" style="width: 300px;">
                    <input type="text" id="no_anggota" name="no_anggota" class="form-control form-control-lg" placeholder="Kode Anggota (F3)" value="<?php echo e(!empty($penjualan->anggota) ? $penjualan->anggota->no_anggota : ''); ?>" onchange="cari_anggota()">
                    <div class="input-group-append">
                        <button class="btn btn-secondary" type="button" id="btn_unlock_anggota" style="display: none;" onclick="unlock_anggota()" title="Klik untuk membuka kunci">
                            <i class="mdi mdi-lock"></i>
                        </button>
                    </div>
                </div>
                <button class="btn btn-primary text-nowrap px-4" type="button" onclick="open_modal_search_produk()">Cari Barang (F4)</button>
                <button class="btn btn-info text-nowrap px-3" type="button" onclick="open_customer_display()" title="Buka tampilan customer di monitor kedua">
                    <i class="mdi mdi-monitor-multiple"></i> Dual Display (F5)
                </button>
            </div>
            <div id="list_items" style="height: calc(100vh - 220px);overflow-y: scroll;"></div>
        </div>
        <div class="w-25 position-relative" style="background-color: #dcdde1;padding: 20px;">
            <div class="form-group">
                <h5><small>Nama Anggota</small><br><span id="nama_anggota"><?php echo e(!empty($penjualan->anggota) ? ($penjualan->anggota->nama_lengkap .' ('. $penjualan->anggota->no_anggota .')') : 'Bukan Anggota (0000)'); ?></span></h5>
            </div>
            <hr>
            <div class="form-group">
                <h3><small>No. Transaksi</small><br><span id="no_transaksi"><?php echo e(!empty($penjualan) ? $penjualan->no_transaksi : '-'); ?></span></h3>
            </div>
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select class="form-control select2" name="metode_pembayaran" id="metode_pembayaran">
                    <?php $__currentLoopData = $metode_pembayaran; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value->id); ?>" ><?php echo e($value->keterangan); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    <!-- Modal Ganti Anggota dengan Alasan -->
    <div class="modal fade" id="modal_unlock_anggota" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="mdi mdi-account-switch"></i> Ganti Anggota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="mdi mdi-account-edit text-warning" style="font-size: 60px;"></i>
                        <h5 class="mt-2">Apakah Anda yakin ingin mengganti anggota?</h5>
                        <p class="text-muted">Anggota saat ini: <strong id="current_anggota_name"></strong></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Alasan Ganti Anggota <span class="text-danger">*</span></strong></label>
                        <textarea class="form-control" id="alasan_unlock" rows="3" placeholder="Masukkan alasan mengganti anggota..." required></textarea>
                        <small class="text-muted">Alasan akan tercatat dalam history transaksi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" onclick="konfirmasi_unlock()">
                        <i class="mdi mdi-lock-open"></i> Konfirmasi Ganti
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_batal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="mdi mdi-alert-circle"></i> Batalkan Transaksi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="mdi mdi-alert-circle-outline text-danger" style="font-size: 60px;"></i>
                        <h5 class="mt-2">Apakah Anda yakin ingin membatalkan transaksi ini?</h5>
                    </div>
                    <div class="form-group">
                        <label><strong>Alasan Pembatalan <span class="text-danger">*</span></strong></label>
                        <textarea class="form-control" id="alasan_batal" rows="3" placeholder="Masukkan alasan pembatalan..." required></textarea>
                        <small class="text-muted">Alasan pembatalan akan tercatat dalam history transaksi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                    <button type="button" class="btn btn-danger" onclick="konfirmasi_batal()">
                        <i class="mdi mdi-delete"></i> Konfirmasi Pembatalan
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="<?php echo e(asset('assets/js/shortcut.js')); ?>"></script>
    <script>
        let _token = '<?php echo e(csrf_token()); ?>', total = 0, total_diskon = 0, tunai = 0, kembali = 0, no_transaksi = '',
            penjualan_id = '', limit = 0, tenor = 0, angsuran = 0, produk_pertama = '', boleh_simpan = true,
            pending_delete_id = '', delete_from_tunda = false;
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
            return await $.post("<?php echo e(url('pos/penjualan_baru/create')); ?>", params).fail((xhr) => {
                console.log(xhr.responseText);
            });
        }

        let search_items = () => {
            $.post("<?php echo e(url('pos/penjualan_baru/item')); ?>/" + penjualan_id + '/search', {_token}, (result) => {
                $list_items.html(result);
            }).fail((xhr) => {
                $list_items.html(xhr.responseText);
            });
        }

        let update_item = (id) => {
            $.post("<?php echo e(url('pos/penjualan_baru/item')); ?>/" + id + '/update', {
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

        // Auto lock anggota
        let anggota_locked = false;

        let lock_anggota = () => {
            anggota_locked = true;
            $no_anggota.prop('readonly', true);
            $no_anggota.css('background-color', '#e9ecef');
            $('#btn_unlock_anggota').show();
        }

        let unlock_anggota = () => {
            // Tampilkan modal untuk minta alasan
            $('#current_anggota_name').text($('#nama_anggota').text());
            $('#alasan_unlock').val('');
            $('#modal_unlock_anggota').modal('show');
            setTimeout(() => $('#alasan_unlock').focus(), 500);
        }

        let konfirmasi_unlock = () => {
            let alasan = $('#alasan_unlock').val().trim();
            if (alasan === '') {
                swal.fire('Error', 'Alasan ganti anggota tidak boleh kosong!', 'error');
                $('#alasan_unlock').focus();
                return;
            }
            
            // Simpan alasan ke keterangan transaksi (opsional: bisa juga buat kolom khusus)
            if (penjualan_id !== '') {
                $.post("<?php echo e(url('pos/penjualan_baru')); ?>/" + penjualan_id + '/update', {
                    _token,
                    keterangan: 'Ganti anggota: ' + alasan
                });
            }
            
            // Unlock field
            anggota_locked = false;
            $no_anggota.prop('readonly', false);
            $no_anggota.css('background-color', '');
            $('#btn_unlock_anggota').hide();
            
            // Tutup modal dan focus ke field
            $('#modal_unlock_anggota').modal('hide');
            setTimeout(() => {
                $no_anggota.val('').focus();
                $('#nama_anggota').html('Bukan Anggota (0000)');
            }, 300);
        }

        let delete_penjualan = () => {
            if (penjualan_id !== '') {
                pending_delete_id = penjualan_id;
                delete_from_tunda = false;
                $('#alasan_batal').val('');
                $('#modal_batal').modal('show');
                setTimeout(() => $('#alasan_batal').focus(), 500);
            }
        }

        let konfirmasi_batal = () => {
            let alasan = $('#alasan_batal').val().trim();
            if (alasan === '') {
                swal.fire('Error', 'Alasan pembatalan tidak boleh kosong!', 'error');
                $('#alasan_batal').focus();
                return;
            }
            
            $.post("<?php echo e(url('pos/penjualan_baru')); ?>/" + pending_delete_id + '/delete', {
                _token,
                alasan_batal: alasan
            }, () => {
                $('#modal_batal').modal('hide');
                if (delete_from_tunda) {
                    // Refresh list tunda
                    open_modal_tunda();
                } else {
                    swal.fire('Berhasil', 'Transaksi berhasil dibatalkan', 'success').then(() => {
                        window.location.href = '<?php echo e(url('pos/penjualan_baru')); ?>';
                    });
                }
            }).fail((xhr) => {
                swal.fire('Error', 'Gagal membatalkan transaksi', 'error');
            });
        }

        let tunda_penjualan = () => {
            window.location.href = '<?php echo e(url('pos/penjualan_baru')); ?>';
        }

        let open_modal_tunda = () => {
            $.post("<?php echo e(url('pos/penjualan_baru/list_tunda')); ?>", {
                _token
            }, (result) => {
                $('#list_penjualan_tunda').html(result);
                $modal_tunda.modal('show');
            }).fail((xhr) => {
                console.log(xhr.responseText);
            });
        }

        let resume_penjualan = (nomor) => {
            window.location.href = '<?php echo e(url('pos/penjualan_baru')); ?>?no_transaksi=' + nomor;
        }

        let cari_anggota = () => {
            let no_anggota = $('#no_anggota').val();
            $.post("<?php echo e(url('pos/penjualan_baru/cari_anggota')); ?>", {_token, no_anggota}, (result) => {
                if (result.error) swal.fire(result.error);
                else {
                    if (penjualan_id !== '') {
                        $.post("<?php echo e(url('pos/penjualan_baru')); ?>/" + penjualan_id + '/update', {
                            _token, no_anggota: result.no_anggota
                        }, (result) => {
                            console.log(result);
                        }).fail((xhr) => {
                            console.log(xhr.responseText);
                        });
                    }
                    $('#nama_anggota').html(result.nama_lengkap + '('+ result.no_anggota +')');
                    
                    // AUTO LOCK setelah anggota ditemukan
                    lock_anggota();
                }
            });
        }

        let delete_item = (id) => {
            $.post("<?php echo e(url('pos/penjualan_baru/item')); ?>/" + id + '/delete', {_token} ,() => {
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
                $.get("<?php echo e(url('pos/penjualan/check_limit')); ?>?fid_anggota=" + anggota_id, (result) => {
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
            $.post("<?php echo e(url('pos/penjualan_baru')); ?>/" + penjualan_id + '/update', {
                _token,
                total_pembayaran: total,
                tunai: remove_commas($dibayar.val()),
                kembali: kembali,
                fid_status: 2,
                diskon: total_diskon,
                fid_metode_pembayaran: $metode_pembayaran.find('option:selected').val(),
            },() => {
                window.location.href = '<?php echo e(url('pos/penjualan_baru')); ?>/' + penjualan_id + '/cetak_struk';
            });
        }

        let search_produk = () => {
            let nama = $('#nama_produk').val();
            $.post("<?php echo e(url('pos/penjualan_baru/cari_produk')); ?>", {_token, nama}, (result) => {
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
            // Gunakan modal yang sama untuk pembatalan dari list tunda
            pending_delete_id = id;
            delete_from_tunda = true;
            $('#alasan_batal').val('');
            $('#modal_batal').modal('show');
            setTimeout(() => $('#alasan_batal').focus(), 500);
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
            $.post("<?php echo e(url('pos/penjualan_baru/item/create')); ?>", {
                _token, kode, jumlah: 1, diskon: 0, fid_penjualan: penjualan_id
            }, (result) => {
                $kode_produk.val('');
                if (result.error) swal.fire(result.error);
                else {
                    if (is_baru === true) {
                        window.location.href = '<?php echo e(url('pos/penjualan_baru')); ?>?no_transaksi=' + no_transaksi;
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
        shortcut.add("F5", () => open_customer_display());
        shortcut.add("F6", () => $kode_voucher.focus());
        shortcut.add("F7", () => delete_penjualan());
        shortcut.add("F8", () => open_modal_tunda());
        shortcut.add("F9", () => tunda_penjualan());
        shortcut.add("F10", () => bayar());
        
        // Customer Display for dual monitor
        let customerDisplayWindow = null;
        let open_customer_display = () => {
            // Store penjualan_id in localStorage for customer display to read
            if (penjualan_id !== '') {
                localStorage.setItem('pos_penjualan_id', penjualan_id);
            }
            
            const url = penjualan_id !== '' 
                ? '<?php echo e(url("pos/customer_display")); ?>/' + penjualan_id 
                : '<?php echo e(url("pos/customer_display")); ?>';
            
            // Open in new window optimized for second monitor
            if (customerDisplayWindow && !customerDisplayWindow.closed) {
                customerDisplayWindow.location.href = url;
                customerDisplayWindow.focus();
            } else {
                customerDisplayWindow = window.open(url, 'CustomerDisplay', 
                    'width=1024,height=768,menubar=no,toolbar=no,location=no,status=no');
            }
        }
        
        // Update localStorage whenever items change so customer display can sync
        let updateCustomerDisplay = () => {
            if (penjualan_id !== '') {
                localStorage.setItem('pos_penjualan_id', penjualan_id);
                localStorage.setItem('pos_updated', Date.now().toString());
            }
        }
        
        // Wrap search_items to also update customer display
        let original_search_items = search_items;
        search_items = () => {
            original_search_items();
            updateCustomerDisplay();
        }

        <?php if(!empty($penjualan)): ?>
            penjualan_id = '<?php echo e($penjualan->id); ?>';
            $metode_pembayaran.val('<?php echo e($penjualan->fid_metode_pembayaran); ?>').trigger('change');
            search_items();
        <?php endif; ?>

        $modal_search_produk.on('hidden.bs.modal', function () {
            $('#nama_produk').val('');
        });

    </script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.kasir2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/penjualan_baru/index.blade.php ENDPATH**/ ?>