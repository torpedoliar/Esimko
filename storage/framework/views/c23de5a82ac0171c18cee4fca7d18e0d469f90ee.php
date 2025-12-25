<?php
    $app='manajemen_barang';
    $page='Data Barang';
    $subpage='Data Barang';
?>

<?php $__env->startSection('title'); ?>
    Data Barang |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <style>
        .table-informasi td,
        .table-informasi th {
            padding: .4rem .75rem;
            vertical-align: top;
            border-top: 1px solid rgb(0 0 0 / 7%);
        }
        .table-informasi tr:first-child td,
        .table-informasi tr:first-child th {
            border-top: none;
        }
        .verti-timeline {
            border-left: 2px dashed #e0e0e0;
            margin: 0 10px;
        }
        .nav-pills .nav-link.active,
        .nav-pills .nav-link.active:hover,
        .nav-pills .show>.nav-link {
            color: #fff;
            background-color: #45a086;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2" style="padding-bottom:10px">
            <div class="row">
                <div class="col-lg-6">
                    <div class="media">
                        <div class="rounded mr-4 produk-wrapper" style="height:120px;width:120px;border:6px solid #e7e7e9">
                            <img src="<?php echo e((!empty($data['produk']->foto) ? asset('storage/'.$data['produk']->foto) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                        </div>
                        <div class="align-self-center media-body">
                            <span class="font-size-15">Kode. <?php echo e($data['produk']->kode); ?></span>
                            <h4><?php echo e($data['produk']->nama_produk); ?></h4>
                            <div class="mt-3">
                                <a class="btn btn-sm btn-secondary" href="<?php echo e(url('manajemen_stok/barang/form?id='.$id)); ?>">Edit Barang</a>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 align-self-center">
                    <div class="mt-4 mt-lg-0">
                        <div class="row">
                            <div class="col-3">
                                <div>
                                    <p class="text-muted text-truncate mb-1">Harga Beli </p>
                                    <h5 class="mb-0 font-size-15">Rp <?php echo e(number_format($data['produk']->harga_beli,'0',',','.')); ?></h5>
                                </div>
                            </div>
                            <div class="col-3">
                                <div>
                                    <p class="text-muted text-truncate mb-1">Margin </p>
                                    <h5 class="mb-0 font-size-15">Rp <?php echo e(number_format($data['produk']->margin_nominal,'0',',','.')); ?></h5>
                                </div>
                            </div>
                            <div class="col-3">
                                <div>
                                    <p class="text-muted text-truncate mb-1">Harga Jual </p>
                                    <h5 class="mb-0 font-size-15">Rp <?php echo e(number_format($data['produk']->harga_jual,'0',',','.')); ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="nav nav-pills mb-2 mt-5">
                <li class="nav-item waves-effect waves-light pr-2">
                    <a class="nav-link <?php echo e(($tab=='informasi' ? 'active' : '')); ?>" href="<?php echo e(url('manajemen_stok/barang/detail?id='.$data['produk']->id.'&tab=informasi')); ?>">Informasi Barang</a>
                </li>
                <li class="nav-item waves-effect waves-light pr-2">
                    <a class="nav-link <?php echo e(($tab=='mutasi' ? 'active' : '')); ?>" href="<?php echo e(url('manajemen_stok/barang/detail?id='.$data['produk']->id.'&tab=mutasi')); ?>">Mutasi Produk</a>
                </li>
            </ul>
        </div>
        <?php echo $__env->make('manajemen_stok.barang.detail.'.$tab, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/manajemen_stok/barang/detail/index.blade.php ENDPATH**/ ?>