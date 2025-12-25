<?php
    $app='manajemen_barang';
    $page='Pembelian Barang';
    $subpage='Pembelian Barang';
?>

<?php $__env->startSection('title'); ?>
    Pembelian Barang |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/trolley.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Pembelian Barang</h4>
                        <p class="text-muted m-0">Menampilkan data pembelian barang ke supplier yang sudah terdaftar</p>
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
                    <a class="btn btn-primary btn-block" href="<?php echo e(url('manajemen_stok/pembelian_baru')); ?>">Tambah Pembelian</a>
                </div>
            </div>
        </div>
        <?php if(count($data['pembelian'])==0): ?>
            <div style="width:100%;text-align:center">
                <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
                <h4 class="mt-2">PEMBELIAN BARANG TIDAK DITEMUKAN</h4>
            </div>
        <?php else: ?>
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom">
                    <thead>
                    <tr>
                        <th class="center">Tanggal</th>
                        <th>No. Pembelian<hr class="line-xs">Nama Supplier</th>
                        <th style="text-align:right">Sub Total</th>
                        <th style="text-align:right">Biaya<br>Tambahan</th>
                        <th style="text-align:right">Total</th>
                        <th class="text-center">Status</th>
                        <th>Created by</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $data['pembelian']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="center" style="width:1px;white-space:nowrap;border-color:<?php echo e($value->status == 1 ? 'green' : 'red'); ?>;"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')); ?></td>
                            <td style="width:1px;white-space:nowrap">
                                No. <?php echo e($value->no_pembelian); ?>

                                <h6><?php echo e($value->supplier->nama_supplier ?? ''); ?></h6>
                            </td>
                            <td style="text-align:right;white-space:nowrap">Rp <?php echo e(number_format($value->subtotal,0,',','.')); ?></td>
                            <td style="text-align:right">Rp <?php echo e(number_format($value->biaya_tambahan,0,',','.')); ?></td>
                            <td style="text-align:right">Rp <?php echo e(number_format($value->total,0,',','.')); ?></td>
                            <td class="text-center"><?php echo e($value->status == 1 ? 'Selesai' : 'Pending'); ?></td>
                            <td style="width:1px;white-space:nowrap">
                                <h6><?php echo e($value->kasir->nama_lengkap); ?></h6>
                                at <?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')); ?>

                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <div class="text-center">
                                    <?php if($value->file != ''): ?>
                                        <a target="_blank" href="<?php echo e(asset('storage/' . $value->file)); ?>" class="text-dark"><i class="bx bx-file h3 m-0"></i></a>
                                    <?php endif; ?>
                                    <a href="<?php echo e(url('manajemen_stok/pembelian_baru?no_pembelian=' . $value->no_pembelian .'&id=' . $value->id )); ?>" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                                    <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                    <form action="<?php echo e(url('manajemen_stok/pembelian/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
                                        <?php echo e(csrf_field()); ?>

                                        <input type="hidden" name="id" value="<?php echo e($value->id); ?>">
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
                <?php echo e($data['pembelian']->links('include.pagination', ['pagination' => $data['pembelian']] )); ?>

            </div>
        <?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/manajemen_stok/pembelian/index.blade.php ENDPATH**/ ?>