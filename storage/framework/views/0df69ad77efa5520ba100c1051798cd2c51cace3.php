<?php
    $app='sinjam';
    $page='Payroll Simpanan';
    $subpage='Payroll Simpanan';

    if(!empty($data['payroll'])){
      if($data['payroll']->fid_status==1 || $data['payroll']->fid_status==0){
        $disabled=$data['status'];
      }
      else{
        $disabled='disabled';
      }
    }
    else{
      $disabled=$data['status'];
    }
?>

<?php $__env->startSection('title'); ?>
    Payroll Simpanan |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <style>
        .verti-timeline {
            border-left: 2px dashed #e0e0e0;
            margin: 0 10px;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/pay-day.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Payroll Simpanan</h4>
                        <p class="text-muted m-0">Menampilkan data payroll simpanan anggota yang sudah diproses oleh petugas</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <form action="" method="get">
                        <input type="text" name="bulan" id="bulan" class="form-control monthpicker" value="<?php echo e($bulan); ?>" onchange="javascript:submit()" autocomplete="off">
                    </form>
                </div>
                <div class="col-md-7">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Anggota">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                        <input type="hidden" name="bulan" value="<?php echo e($bulan); ?>">
                    </form>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-lg-6">
                            <button class="btn btn-primary btn-block" id="button_proses" <?php if($disabled=='disabled'): ?> <?php echo e($disabled); ?>  <?php else: ?> onclick="confirm_proses()" <?php endif; ?>>Proses Payroll</button>
                        </div>
                        <div class="col-lg-6">
                            <button class="btn btn-danger btn-block" id="button_hapus" onclick="confirm_hapus()">Hapus Payroll</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if($data['payroll']==null): ?>
            <div style="width:100%;text-align:center">
                <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
                <h4 class="mt-3">Payroll Simpanan Belum Diproses</h4>
            </div>
        <?php else: ?>
            <div class="row mt-4 mb-4">
                <div class="col">
                    <div style="height:100%">
                        <?php if(count($data['payroll']->data)==0): ?>
                            <div style="width:100%;text-align:center">
                                <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
                                <h4 class="mt-3">Data Anggota tidak Ditemukan</h4>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-middle table-custom">
                                    <thead>
                                    <tr>
                                        <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
                                        <?php $__currentLoopData = $data['jenis-simpanan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <th style="text-align:right;width:120px">Jumlah <?php echo e(str_replace('Setoran','',$value->jenis_transaksi)); ?></th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__currentLoopData = $data['payroll']->data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="media">
                                                    <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                                                        <img src="<?php echo e((!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )); ?>" alt="" class="rounded-circle">
                                                    </div>
                                                    <div class="media-body align-self-center">
                                                        <p class="text-muted mb-0">No. <?php echo e($value->no_anggota); ?></p>
                                                        <h5 class="text-truncate font-size-15"><a href="<?php echo e(url('anggota/detail?id='.$value->id)); ?>" class="text-dark"><?php echo e($value->nama_lengkap); ?></a></h5>
                                                    </div>
                                                </div>
                                            </td>
                                            <?php $__currentLoopData = $data['jenis-simpanan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key2 => $value2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $label=str_replace(' ','_',strtolower($value2->jenis_transaksi));
                                                ?>
                                                <td style="text-align:right"><?php echo e(number_format($value->$label,'0',',','.')); ?></td>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php echo e($data['payroll']->data->links('include.pagination', ['pagination' => $data['payroll']->data] )); ?>

                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto">
                    <div style="border-left:1px solid #dedede;padding:20px 20px;height:100%;width:320px">
                        <div class="center">
                            <img src="<?php echo e(asset('assets/images/'.$data['payroll']->icon)); ?>" style="width:65px" class="">
                            <h5 class="mb-2 mt-2"><?php echo e($data['payroll']->status); ?></h5>
                            <p><?php echo e($data['payroll']->keterangan); ?></p>
                            <a target="_blank" href="<?php echo e(url('simpanan/payroll?bulan=' . $bulan . '&mode=cetak')); ?>" class="btn btn-secondary" >Cetak Payroll</a>
                            <?php if($data['payroll']->fid_status==1): ?>
                                <button class="btn btn-warning" onclick="confirm_payroll(2)">Sudah Dikirim</button>
                            <?php elseif($data['payroll']->fid_status==2): ?>
                                <button class="btn btn-info" onclick="confirm_payroll(3)">Konfirmasi Pembayaran</button>
                            <?php else: ?>
                                <button class="btn btn-danger" onclick="confirm_payroll(1)">Batalkan Verifikasi</button>
                            <?php endif; ?>
                            <form action="<?php echo e(url('simpanan/payroll/verifikasi')); ?>" method="post" id="verifikasi_payroll" >
                                <?php echo e(csrf_field()); ?>

                                <input type="hidden" name="bulan" value="<?php echo e($bulan); ?>">
                                <input type="hidden" name="status" id="status">
                            </form>
                        </div>
                        <h5 class="mb-3 mt-5">Riwayat Transaksi</h5>
                        <ul class="verti-timeline list-unstyled">
                            <li class="event-list">
                                <div class="event-timeline-dot">
                                    <i class="bx bx-right-arrow-circle"></i>
                                </div>
                                <h6><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($data['payroll']->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($data['payroll']->created_at,'H:i:s')); ?></h6>
                                <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500"><?php echo e($data['payroll']->nama_lengkap); ?></span></p>
                            </li>
                            <?php $__currentLoopData = \App\Helpers\GlobalHelper::get_verifikasi_transaksi($data['payroll']->id,'payroll_simpanan'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="event-list">
                                    <div class="event-timeline-dot">
                                        <i class="bx bx-right-arrow-circle"></i>
                                    </div>
                                    <h6><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')); ?></h6>
                                    <p class="text-muted"><?php echo e($value->caption); ?> <span style="font-weight:500"><?php echo e($value->nama_lengkap); ?></span></p>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <form action="<?php echo e(url('simpanan/payroll/proses')); ?>" id="proses_simpanan" method="post">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="bulan" value="<?php echo e($bulan); ?>">
    </form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>
        function confirm_proses(){
            Swal.fire({
                title: "Are you sure?",
                text: "Apakah anda yakin ingin memproses simpanan anggota pada bulan ini",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Proses Simpanan'
            }).then((result) => {
                if (result.value == true) {
                    $('#button_proses').html('Proses ...');
                    $('#proses_simpanan').submit();
                }
            });
        }

        function confirm_payroll(status){
            if(status==2){
                text="Apakah Data Payroll Simpanan sudah dikirim ke HRD Perusahaan?";
            }
            else if(status==3){
                text="Apakah Pembayaran Payroll dari Perusahaan sudah diterima oleh Koperasi?";
            }
            else{
                text="Apakah anda yakin ingin membatalkan Payroll Simpanan ini agar bisa diproses ulang";
            }
            $('#status').val(status);
            Swal.fire({
                title: "Are you sure?",
                type:"question",
                text:text,
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value == true) {
                    $('#verifikasi_payroll').submit();
                }
            });
        }

        let confirm_hapus = () => {
            Swal.fire({
                title: "Hapus Payroll?",
                text: "Apakah anda yakin ingin menghapus payroll ?",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Ya'
            }).then((result) => {
                if (result.value === true) proses_hapus();
            });
        }

        let proses_hapus = () => {
            Swal.fire({
                title: "Konfirmasi Terakhir?",
                text: "Apakah benar benar yakin ingin menghapus payroll bulan ini ?",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Proses Hapus'
            }).then((result) => {
                if (result.value === true) {
                    let bulan = $('#bulan').val();
                    window.location.href = "<?php echo e(url('simpanan/payroll/hapus')); ?>?bulan=" + bulan;
                }
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/simpanan/payroll/index.blade.php ENDPATH**/ ?>