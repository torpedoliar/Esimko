<?php
    $app='pos';
    $page='Laporan';
    $subpage='Laporan Produk';
?>

<?php $__env->startSection('title'); ?>
    Laporan Produk |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/market.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Data Laporan Produk</h4>
                        <p class="text-muted m-0">Menampilkan laporan hasil produk</p>
                    </div>
                </div>
            </div>

            <form id="form_search">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <div class="col-md-2">
                        <input type="hidden" id="fid_produk" name="fid_produk" />
                        <input type="text" class="form-control" name="kode" id="kode" placeholder="Kode Produk" />
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="nama" id="nama_produk" placeholder="Nama Produk" readonly />
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="tanggal_awal" placeholder="Tanggal Awal" />
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="tanggal_akhir" placeholder="Tanggal Akhir" />
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-dark btn-block" type="submit">Search</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-success btn-block" type="button" onclick="export_Excel()">Export</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-primary btn-block" type="button" onclick="cetak()">Cetak</button>
                    </div>
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
            data.paginate = 10;
            data.page = page;
            $.post("<?php echo e(url('pos/laporan_mutasi/search')); ?>", data, (result) => {
                $table.html(result);
            }).fail((xhr) => {
                $table.html(xhr.responseText);
            });
        }
        search_data();

        let export_Excel = () => {
            let data = $form_search.serialize();
            window.open("<?php echo e(url('pos/laporan_mutasi/excel')); ?>?" + data, '_blank');
        }

        let cetak = () => {
            let data = $form_search.serialize();
            window.open("<?php echo e(url('pos/laporan_mutasi/cetak')); ?>?" + data, '_blank');
        }

        let $kode = $('#kode'), $nama_produk = $('#nama_produk');
        $kode.change(() => {
            let kode = $kode.val();
            $.get("<?php echo e(url('manajemen_stok/stok_opname/search/produk')); ?>?kode=" + kode, (result) => {
                $('#nama_produk').val(result.nama_produk);
                $('#fid_produk').val(result.id);
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/laporan/mutasi/index.blade.php ENDPATH**/ ?>