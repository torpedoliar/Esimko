<?php
    $app='manajemen_barang';
    $page='Manajemen Stok';
    $subpage='Pengembalian Barang';
?>

<?php $__env->startSection('title'); ?>
    Retur Barang |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/return-box.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Retur Barang</h4>
                        <p class="text-muted m-0">Menampilkan data retur barang ke supplier yang dibeli</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Transaksi">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-3">
                    <a class="btn btn-primary btn-block" href="<?php echo e(url('manajemen_stok/return/form')); ?>">Tambah Retur</a>
                </div>
            </div>
        </div>
        <?php if(count($data['retur'])==0): ?>
            <div style="width:100%;text-align:center">
                <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
                <h4 class="mt-2">RETUR BARANG TIDAK DITEMUKAN</h4>
            </div>
        <?php else: ?>
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom">
                    <thead>
                    <tr>
                        <th class="center">Tanggal</th>
                        <th>No. Retur<hr class="line-xs">Nama Supplier</th>
                        <th>Nama Barang</th>
                        <th class="center" style="white-space:nowrap">Metode Retur<hr class="line-xs">Jumlah</th>
                        <th style="text-align:right;white-space:nowrap">Harga Beli<hr class="line-xs">Total</th>
                        <th>Created by</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $data['retur']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="center" style="width:1px;white-space:nowrap"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')); ?></td>
                            <td style="width:1px;white-space:nowrap">
                                <div style="white-space:nowrap"><?php echo e($value->no_retur); ?></div>
                                <div style="font-weight:500"><?php echo e($value->nama_supplier); ?></div>
                            </td>
                            <td>
                                <div class="media">
                                    <div class="rounded mr-2 produk-wrapper" style="height:50px;width:50px">
                                        <img src="<?php echo e((!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                                    </div>
                                    <div class="align-self-center media-body">
                                        <span>Kode. <?php echo e($value->kode); ?></span>
                                        <h6><?php echo e($value->nama_produk); ?></h6>
                                    </div>
                                </div>
                            </td>
                            <td class="center">
                                <h6><?php echo e($value->metode); ?></h6>
                                <?php echo e($value->jumlah); ?> <?php echo e($value->satuan); ?>

                            </td>
                            <td style="text-align:right">
                                Rp <?php echo e(number_format($value->harga,'0',',','.')); ?>

                                <h6>Rp <?php echo e(number_format($value->total,'0',',','.')); ?></h6>
                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <h6>(<?php echo e($value->created_by); ?>) <?php echo e($value->nama_lengkap); ?></h6>
                                at <?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')); ?>

                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <div class="text-center">
                                    <a href="<?php echo e(url('manajemen_stok/return/form?id='.$value->fid_retur_pembelian)); ?>" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                                    <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->fid_retur_pembelian); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                    <form action="<?php echo e(url('manajemen_stok/return/proses')); ?>" method="post" id="hapus<?php echo e($value->fid_retur_pembelian); ?>">
                                        <?php echo e(csrf_field()); ?>

                                        <input type="hidden" name="id" value="<?php echo e($value->fid_retur_pembelian); ?>">
                                        <input type="hidden" name="action" value="delete">
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="mb-4">
                <?php echo e($data['retur']->links('include.pagination', ['pagination' => $data['retur']] )); ?>

            </div>
        <?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/manajemen_stok/return/index.blade.php ENDPATH**/ ?>