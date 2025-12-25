<?php
    $app='sinjam';
    $page='Bunga Simpanan';
    $subpage='Bunga Simpanan';
    $disabled = $data['status'];
?>

<?php $__env->startSection('title'); ?>
    Bunga Simpanan |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/care.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Bunga Simpanan</h4>
                        <p class="text-muted m-0">Menampilkan data bunga simpanan sukarela yang sudah diposting oleh petugas setiap hari</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10">
                    <form action="" method="get">
                        <div class="row">
                            <div class="col-lg-2">
                                <input type="text" name="tanggal" class="form-control datepicker" value="<?php echo e($tanggal); ?>"  autocomplete="off">
                            </div>
                            <div class="col-lg-10">
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Anggota">
                                    <div class="input-group-append">
                                        <button class="btn btn-dark" type="submit">Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-block" onclick="confirm_proses()"  >Posting Bunga</button>
                </div>
            </div>
        </div>
        <?php if(count($data['bunga-simpanan']) == 0): ?>
            <div style="width:100%;text-align:center">
                <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
                <h4 class="mt-3">Bunga Simpanan belum Diposting</h4>
            </div>
        <?php else: ?>
            <div class="row mt-4 mb-4">
                <div class="col">
                    <div style="height::100%">
                        
                        
                        
                        
                        
                        
                        <div class="table-responsive">
                            <table class="table table-middle table-custom">
                                <thead>
                                <tr>
                                    <th>No. Anggota</th>
                                    <th>Nama Lengkap</th>
                                    <th>Tanggal</th>
                                    <th style="text-align:right">Nominal</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $data['bunga-simpanan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($value->anggota->no_anggota); ?></td>
                                        <td><?php echo e($value->anggota->nama_lengkap); ?></td>
                                        <td><?php echo e(format_date($value->tanggal)); ?></td>
                                        <td style="text-align:right">Rp <?php echo e(format_number($value->nominal)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <?php echo e($data['bunga-simpanan']->links('include.pagination', ['pagination' => $data['bunga-simpanan']] )); ?>

                        
                    </div>
                </div>
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
            </div>
        <?php endif; ?>
    </div>
    <form action="<?php echo e(url('simpanan/bunga/proses')); ?>" id="proses_bunga" method="post">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="tanggal" value="<?php echo e($tanggal); ?>">
    </form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>
        function confirm_proses(){
            Swal.fire({
                title: "Are you sure?",
                text: "Apakah anda yakin ingin memproses bunga simpanan anggota untuk hari ini",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Proses Simpanan'
            }).then((result) => {
                if (result.value == true) {
                    $('#proses_bunga').submit();
                }
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/simpanan/bunga/index.blade.php ENDPATH**/ ?>