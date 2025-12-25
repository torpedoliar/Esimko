<?php
    $app='pos';
    $page='Laporan';
    $subpage='Laporan Penjualan';
?>

<?php $__env->startSection('title'); ?>
    Laporan Penjualan |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/market.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Data Laporan Penjualan</h4>
                        <p class="text-muted m-0">Menampilkan laporan hasil penjualan</p>
                    </div>
                </div>
            </div>

            <form id="form_search" class="mb-3">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <div class="col-md-2">
                        <label>Kelompok Barang</label>
                        <select class="select2" style="width:100%" id="kelompok" name="kelompok"></select>
                    </div>
                    <div class="col-md-2">
                        <label>Kategori</label>
                        <select class="select2" style="width:100%" id="kategori" name="kategori"></select>
                    </div>
                    <div class="col-md-2">
                        <label>Sub Kategori</label>
                        <select class="select2" style="width:100%" id="sub_kategori" name="sub_kategori"></select>
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal Awal</label>
                        <input type="text" class="form-control datepicker" name="tanggal_awal" />
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal Akhir</label>
                        <input type="text" class="form-control datepicker" name="tanggal_akhir" />
                    </div>
                    <div class="col-md-2">
                        <label>Metode Penjualan</label>
                        <select class="select2" style="width:100%" id="metode_pembayaran" name="metode_pembayaran">
                            <option value="">Semua Metode</option>
                            <?php $__currentLoopData = $list_metode; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($item->id); ?>"><?php echo e($item->metode_pembayaran); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 mt-3">
                        <label>Jenis Penjualan</label>
                        <select class="select2" style="width:100%" id="jenis_penjualan" name="jenis_penjualan">
                            <option value="">Semua</option>
                            <option>Konsinyasi</option>
                            <option>Belanja Langsung</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-3">
                        <label>Jenis Belanja</label>
                        <select class="select2" style="width:100%" id="jenis_belanja" name="jenis_belanja">
                            <option value="">toko</option>
                            <option>konsinyasi</option>
                            <option>online</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-3">
                        <label>No.Anggota</label>
                        <input type="text" class="form-control" name="no_anggota" />
                    </div>
                    <div class="col-md-2 mt-3">
                        <label>Nama Barang</label>
                        <input type="text" class="form-control" name="nama_barang" />
                    </div>
                    <div class="col-md-2 mt-3">
                        <label>No.Penjualan</label>
                        <input type="text" class="form-control" name="no_penjualan" />
                    </div>
                    <div class="col-md-2 mt-3">
                        <label>Per Halaman</label>
                        <select class="select2" style="width:100%" name="paginate">
                            <option value="10">10</option>
                            <option value="100">100</option>
                            <option value="99999999">Semua</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex flex-row justify-content-end mt-3" style="gap: 10px;">
                    <button class="btn btn-dark btn-block" type="submit">Search</button>
                    <button class="btn btn-primary px-4" type="button" onclick="cetak()">Cetak</button>
                    <button class="btn btn-success px-4" type="button" onclick="export_Excel()">Export</button>
                </div>
            </form>

            <div id="table"></div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>
        let $form_search = $('#form_search'),
            $table = $('#table'),
            selected_page = 1;

        $form_search.submit((e) => {
            e.preventDefault();
            search_data();
        });

        let search_data = (page = 1) => {
            if (page.toString() === '+1') selected_page++;
            else if (page.toString() === '-1') selected_page--;
            else selected_page = page;

            let data = get_form_data($form_search);
            data.page = page;
            $table.html('Loading ... ');
            $.post("<?php echo e(url('pos/laporan_penjualan/search')); ?>", data, (result) => {
                $table.html(result);
            }).fail((xhr) => {
                $table.html(xhr.responseText);
            });
        }
        search_data();

        let export_Excel = () => {
            let data = $form_search.serialize();
            window.open("<?php echo e(url('pos/laporan_penjualan/excel')); ?>?" + data, '_blank');
        }

        let cetak = () => {
            let data = $form_search.serialize();
            window.open("<?php echo e(url('pos/laporan_penjualan/cetak')); ?>?" + data, '_blank');
        }

        selected_kelompok = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kelompok'] !='all'  ? Session::get('filter_produk')['kelompok'] : 'all')); ?>';
        selected_kategori = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kategori'] !='all' ? Session::get('filter_produk')['kategori'] : 'all')); ?>';
        selected_subkategori = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['sub_kategori'] !='all' ? Session::get('filter_produk')['sub_kategori'] : 'all')); ?>';
        selected_is_aktif = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['is_aktif'] != 'all' ? Session::get('filter_produk')['is_aktif'] : 'all')); ?>';
        function get_kategori(select_target, parent_id, selected){
            $.get("<?php echo e(url('api/get_kategori')); ?>/"+parent_id+'/'+selected, function (result) {
                $selectElement = $('#'+select_target);
                $selectElement.empty();
                $.each(result, function (i, value) {
                    $selectElement.append('<option data-id="'+value.id+'" value="'+value.id+'" '+value.selected+' >'+value.nama_kategori+'</option>');
                });
                $selectElement.trigger('change');
            });
        }

        get_kategori('kelompok','0', selected_kelompok);
        $('#kelompok').change(function () {
            let id = $(this).find('option:selected').attr('data-id');
            get_kategori('kategori',id, selected_kategori);
        });
        $('#kategori').change(function () {
            let id = $(this).find('option:selected').attr('data-id');
            get_kategori('sub_kategori',id, selected_subkategori);
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/laporan/penjualan/index.blade.php ENDPATH**/ ?>