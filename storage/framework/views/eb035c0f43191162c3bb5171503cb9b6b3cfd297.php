<?php
    $app='manajemen_barang';
    $page='Penyesuaian Stok';
    $subpage='Penyesuaian Stok';
?>

<?php $__env->startSection('title'); ?>
    Penyesuaian Stok |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/boxes.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Penyesuaian Stok</h4>
                        <p class="text-muted m-0">Memproses penyesuaian jumlah stok barang</p>
                    </div>
                </div>
            </div>
            <form id="form_search">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="search" placeholder="Cari Data Produk">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="tanggal_awal" placeholder="Tanggal Awal" />
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="tanggal_akhir" placeholder="Tanggal Akhir" />
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-success btn-block" type="submit">Cari</button>
                    </div>
                    <div class="col-md-2">
                        <a class="btn btn-primary btn-block" href="<?php echo e(url('manajemen_stok/stok_opname/create')); ?>">Tambah</a>
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
            selected_page = <?php echo e($halaman); ?>;

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
            $.post("<?php echo e(url('manajemen_stok/stok_opname/search')); ?>", data, (result) => {
                $table.html(result);
            }).fail((xhr) => {
                $table.html(xhr.responseText);
            });
        }
        search_data();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/manajemen_stok/stok_opname/index.blade.php ENDPATH**/ ?>