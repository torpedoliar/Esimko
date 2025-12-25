<?php
    $app='pos';
    $page='Penjualan';
    $subpage='Penjualan';
?>

<?php $__env->startSection('title'); ?>
    Penjualan |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/market.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Data Penjualan</h4>
                        <p class="text-muted m-0">Menampilkan data penjualan barang toko ke pembeli atau anggota</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <form action="" method="get" id="status_form" >
                        <input type="hidden" value="<?php echo e($status); ?>" id="status_id" name="status" value="">
                        <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
                            <option value="#282828" data-id="all">Semua Status</option>
                            <?php $__currentLoopData = $data['status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value->color); ?>" <?php echo e(($status == $value->id ? 'selected' : '')); ?> data-id="<?php echo e($value->id); ?>" ><?php echo e($value->status); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </form>
                </div>
                <div class="col-md-9">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Search Transaksi Penjualan">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2">

                </div>
            </div>
        </div>
        <?php if(count($data['penjualan'])==0): ?>
            <div style="width:100%;text-align:center">
                <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
                <h4 class="mt-2">Data Penjualan Barang tidak Ditemukan</h4>
            </div>
        <?php else: ?>
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom">
                    <thead>
                    <tr>
                        <th>No. Penjualan<hr class="line-xs">Waktu</th>
                        <th>Pembeli</th>
                        <th class="center">Jumlah<br>Barang</th>
                        <th class="center">Metode<br>Pembayaran</th>
                        <th style="text-align:right">Total<br>Pembayaran</th>
                        <th>Kasir</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $data['penjualan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>">
                                <h6><?php echo e($value->no_transaksi); ?></h6>
                                <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,'d/m/Y, H:i:s')); ?>

                            </td>
                            <td>
                                <div class="media">
                                    <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                                        <img src="<?php echo e((!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )); ?>" alt="" class="rounded-circle">
                                    </div>
                                    <div class="media-body align-self-center">
                                        <?php if(!empty($value->no_anggota)): ?>
                                            <p class="text-muted mb-0">No. <?php echo e($value->no_anggota); ?></p>
                                            <h5 class="text-truncate font-size-13"><a href="<?php echo e(url('anggota/detail?id='.$value->id)); ?>" class="text-dark"><?php echo e($value->nama_lengkap); ?></a></h5>
                                        <?php else: ?>
                                            <p class="text-muted mb-0">No. 0000</p>
                                            <h5 class="text-truncate font-size-13">Bukan Anggota</h5>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="center"><?php echo e($value->jumlah); ?></td>
                            <td class="center"><?php echo e($value->metode_pembayaran); ?></td>
                            <td style="text-align:right;white-space:nowrap">Rp <?php echo e(number_format($value->total_pembayaran,0,',','.')); ?></td>
                            <td style="width:1px;white-space:nowrap">
                                <?php if($value->kasir==null): ?>
                                    <span>Belum Diproses<br>oleh Kasir</span>
                                <?php else: ?>
                                    <span class="text-muted">No. <?php echo e($value->kasir); ?></span>
                                    <h6><?php echo e($value->nama_petugas); ?></h6>
                                <?php endif; ?>
                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <div class="text-center">
                                    <?php if($value->fid_status==5): ?>
                                        <a href="<?php echo e(url('pos/penjualan_baru?no_transaksi='.$value->no_transaksi)); ?>" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                                    <?php else: ?>
                                        <a href="<?php echo e(url('pos/penjualan/detail?id='.$value->id)); ?>" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="mb-4">
                <?php echo e($data['penjualan']->links('include.pagination', ['pagination' => $data['penjualan']] )); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>
        function formatStatus(status) {
            var $status = $(
                '<span style="display:flex;align-items:center;"><div class="indikator-status mr-2" style="background:'+status.id+'"></div>'+status.text+'</span>'
            );
            return $status;
        };

        $(".select2-status").select2({
            templateResult: formatStatus
        });

        function pilih_status(){
            let id = $('#status_color').find('option:selected').attr('data-id');
            $('#status_id').val(id);
            $('#status_form').submit();
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/penjualan/index.blade.php ENDPATH**/ ?>