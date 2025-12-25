<?php
    $app='sinjam';
    $page='Pengajuan Pinjaman';
    $subpage='Pengajuan Pinjaman';
?>

<?php $__env->startSection('title'); ?>
    Pengajuan Pinjaman |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')); ?>" rel="stylesheet" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="<?php echo e(asset('assets/images/icon-page/save-money.png')); ?>" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Pengajuan Pinjaman</h4>
                    <p class="text-muted m-0">Menampilkan detail pengajuan pinjaman anggota yang sudah diinput oleh petugas atau anggota</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="<?php echo e(($data['pinjaman']->fid_status == 5 ? 'col-md-12' : 'col-md-8')); ?>">
                <div class="card">
                    <div class="card-body">
                        <div class="center mb-5">
                            <img src="<?php echo e(asset('assets/images/'.$data['pinjaman']->icon)); ?>" style="width:80px">
                            <h4 class="mt-3"><?php echo e($data['keterangan']->label ?? 'Dibatalkan'); ?></h4>
                            <p><?php echo e($data['keterangan']->keterangan ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="card-header">
                        <ul class="nav nav-pills" role="tablist">
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link active" data-toggle="tab" href="#informasi" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                    <span class="d-none d-sm-block">Informasi Pinjaman</span>
                                </a>
                            </li>
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link" data-toggle="tab" href="#angsuran" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                    <span class="d-none d-sm-block">Angsuran Pinjaman</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="informasi" role="tabpanel">
                                <h5 class="mb-3">Informasi Transaksi</h5>
                                <table class="table table-informasi">
                                    <tr>
                                        <th width="180px">No. Anggota</th>
                                        <th width="10px">:</th>
                                        <td><?php echo e($data['pinjaman']->no_anggota); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nama Lengkap</th>
                                        <th>:</th>
                                        <td><?php echo e($data['pinjaman']->nama_lengkap); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Jenis Transaksi</th>
                                        <th>:</th>
                                        <td><?php echo e($data['pinjaman']->jenis_transaksi); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Metode Transaksi</th>
                                        <th>:</th>
                                        <td><?php echo e($data['pinjaman']->metode_transaksi); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Jumlah Pinjaman</th>
                                        <th>:</th>
                                        <td>Rp <?php echo e(format_number(str_replace('-','',$data['pinjaman']->nominal))); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Angsuran</th>
                                        <th>:</th>
                                        <td>Rp <?php echo e(format_number(str_replace('-','',$data['pinjaman']->total_angsuran))); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Sisa Tenor</th>
                                        <th>:</th>
                                        <td><?php echo e($data['pinjaman']->sisa_tenor); ?> dari <?php echo e($data['pinjaman']->tenor); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Sisa Pinjaman</th>
                                        <th>:</th>
                                        <td>Rp <?php echo e(format_number(str_replace('-','',$data['pinjaman']->sisa_pinjaman))); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Keterangan</th>
                                        <th>:</th>
                                        <td><?php echo e((!empty($data['pinjaman']->keterangan) ? $data['pinjaman']->keterangan : 'Tidak ada Keterangan')); ?></td>
                                    </tr>
                                </table>
                                <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
                                <ul class="verti-timeline list-unstyled">
                                    <li class="event-list">
                                        <div class="event-timeline-dot">
                                            <i class="bx bx-right-arrow-circle"></i>
                                        </div>
                                        <h6><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($data['pinjaman']->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($data['pinjaman']->created_at,'H:i:s')); ?></h6>
                                        <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500"><?php echo e($data['pinjaman']->nama_petugas); ?></span></p>
                                    </li>
                                    <?php $__currentLoopData = \App\Helpers\GlobalHelper::get_verifikasi_transaksi($id,'transaksi'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                            <div class="tab-pane" id="angsuran" role="tabpanel">
                                <table class="table table-middle table-bordered table-hover mt-3">
                                    <thead class="thead-light">
                                    <tr>
                                        <th class="center">Angsuran<br>Ke</th>
                                        <th style="text-align:right">Sisa<br>Pinjaman</th>
                                        <th class="center">Bunga<br>(%)</th>
                                        <th style="text-align:right">Angsuran<br>Pokok</th>
                                        <th style="text-align:right">Angsuran<br>Bunga</th>
                                        <th style="text-align:right">Total<br>Angsuran</th>
                                        <th class="center">Status</th>
                                        <th class="center"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__currentLoopData = $data['angsuran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td style="width:1px;white-space:nowrap" class="center"><?php echo e($value->angsuran_ke); ?> <?php echo e($value->id); ?></td>
                                            <td style="text-align:right"><?php echo e(format_number($value->sisa_hutang,'0',',','.')); ?></td>
                                            <td class="center"><?php echo e($value->bunga); ?></td>
                                            <td style="text-align:right"><?php echo e(format_number($value->angsuran_pokok,'0',',','.')); ?></td>
                                            <td style="text-align:right"><?php echo e(format_number($value->angsuran_bunga,'0',',','.')); ?></td>
                                            <td style="text-align:right"><?php echo e(format_number($value->angsuran_pokok+$value->angsuran_bunga,'0',',','.')); ?></td>
                                            <td class="center" style="width:1px;white-space:nowrap">
                                                <span style="background:<?php echo e($value->color); ?>;padding:3px 6px;color:#fff;font-size:11px"><?php echo e($value->status_angsuran); ?></span>
                                            </td>
                                            <td class="center">
                                                <?php if(empty($value->payroll) && empty($value->transaksi)): ?>
                                                    <button class="btn btn-danger btn-sm rounded-0">Hapus</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="pull-right">
                            <a href="<?php echo e(url('pinjaman/pengajuan')); ?>" class="btn btn-dark">Kembali</a>
                            <?php if($data['pinjaman']->fid_status <= 2 ): ?>
                                <a class="btn btn-primary"  href="<?php echo e(url('pinjaman/pengajuan/form?id='.$id)); ?>">Edit Pinjaman</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if($data['pinjaman']->fid_status!=5): ?>
                <div class="col-md-4">
                    <div style="position:sticky;top:100px;width:100%;z-index:100">
                        <?php if($data['pinjaman']->sisa_tenor == $data['pinjaman']->tenor): ?>
                            <div class="alert alert-secondary mb-4" role="alert">
                                <h5 class="font-size-18 mb-3">Verifikasi Transaksi</h5>
                                <?php if($data['pinjaman']->fid_status==1): ?>
                                    <p>Harap segera melakukan verifikasi terhadap pengajuan pinjaman ini.</p>
                                <?php elseif($data['pinjaman']->fid_status==2): ?>
                                    <p>Terimakasih sudah melakukan verifikasi terhadap pengajuan pinjaman ini, Silahkan batalkan verifikasi jika ingin mengubah keputusan verifikasi</p>
                                <?php elseif($data['pinjaman']->fid_status==3): ?>
                                    <p>Terimakasih sudah menyetujui pengajuan pinjaman ini, Silahkan menunggu anggota melakukan konfirmasi dan penandatangan dokumen di kantor koperasi</p>
                                <?php else: ?>
                                    <p>Terimakasih sudah menyelesaikan proses pengajuan pinjaman ini. Silahkan batalkan verifikasi sebelum pinjaman ini sudah diangsur</p>
                                <?php endif; ?>
                                <div class="mb-2">
                                    <?php if($data['pinjaman']->fid_status==1): ?>
                                        <button class="btn btn-danger" onclick="confirm_verifikasi(2)">Ditolak</button>
                                        <button class="btn btn-info" onclick="confirm_verifikasi(3)">Disetujui</button>
                                    <?php elseif($data['pinjaman']->fid_status==3): ?>
                                        <button class="btn btn-dark" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
                                        <button class="btn btn-primary" onclick="confirm_verifikasi(4)">Selesai</button>
                                    <?php else: ?>
                                        <button class="btn btn-dark" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary mb-4" role="alert">
                                <h5 class="font-size-18 mb-3">Verifikasi Transaksi</h5>
                                <p>Maaf anda sudah tidak bisa melakukan pembatalan verifikasi, karena pinjaman ini sudah dalam proses angsuran.</p>
                            </div>
                        <?php endif; ?>
                        <?php if($data['pinjaman']->fid_status <= 2 ): ?>
                            <div class="alert alert-danger  mb-4" role="alert">
                                <h5 class="font-size-18">Batalkan Transaksi</h5>
                                <p class="mt-3">Silahkan melakukan pembatalan pengajuan pinjaman sebelum diverifikasi oleh petugas</p>
                                <button class="btn btn-danger mb-2" onclick="confirm_verifikasi(5)">Batalkan Pinjaman</button>
                            </div>
                        <?php endif; ?>
                        <?php if($data['pinjaman']->fid_status == 4 ): ?>
                            <div class="alert <?php echo e(($data['pinjaman']->sisa_tenor > $data['pinjaman']->tenor/2 ? 'alert-danger' : 'alert-warning')); ?> mb-4" role="alert">
                                <h5 class="font-size-18">Pelunasan Pinjaman</h5>
                                <?php if($data['pinjaman']->sisa_tenor > $data['pinjaman']->tenor/2): ?>
                                    <p class="mt-3">Sisa tenor pinjaman masih diatas 50% dari tenor pinjaman, pinjaman ini belum bisa dilakukan pelunasan.</p>
                                <?php else: ?>
                                    <p class="mt-3">Silahkan melakukan pengajuan pelunasan pinjaman dengan mengisi formulir dibawah ini</p>
                                <?php endif; ?>
                                <button class="btn <?php echo e(($data['pinjaman']->sisa_tenor > $data['pinjaman']->tenor/2 ? 'btn-danger' : 'btn-warning')); ?> mb-2" data-toggle="modal" data-target="#formulir_pelunasan" >Formulir Pelunasan</button>
                            </div>
                        <?php elseif($data['pinjaman']->fid_status == 6 ): ?>
                            <div class="alert alert-success  mb-4" role="alert">
                                <h5 class="font-size-18">Pelunasan Pinjaman</h5>
                                <?php if($data['pinjaman']->sisa_tenor == 0 ): ?>
                                    <p class="mt-3">Pinjaman ini sudah dilunasi dengan berakhirnya sisa tenor pinjaman.</p>
                                <?php else: ?>
                                    <p class="mt-3">Pinjaman ini sudah dilunasi dengan melukan pelunasan sebelum masa tenor selesai, Silahkan batalkan pelunasan jika terdapat kesalahan dalam proses pelunasannya.</p>
                                    <button class="btn btn-secondary mb-2" onclick="confirm_verifikasi(6)" >Batalkan Pelunasan</button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="modal fade" id="formulir_pelunasan">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Formulir Pelunasan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo e(url('pinjaman/pengajuan/pelunasan')); ?>" method="post">
                    <?php echo e(csrf_field()); ?>

                    <div class="modal-body">
                        <table class="table table-informasi">
                            <tr>
                                <th class="align-middle">Tanggal Pelunasan</th>
                                <th class="align-middle">:</th>
                                <td><input type="text" class="form-control datepicker text-right" name="tanggal_pelunasan" id="tanggal_pelunasan" required></td>
                            </tr>
                            <tr>
                                <th width="180px">Jenis Transaksi</th>
                                <th width="10px">:</th>
                                <td><?php echo e($data['pinjaman']->jenis_transaksi); ?></td>
                            </tr>
                            <tr>
                                <th>Jumlah Pinjaman</th>
                                <th>:</th>
                                <td>Rp <?php echo e(format_number(str_replace('-','',$data['pinjaman']->nominal))); ?></td>
                            </tr>
                            <tr>
                                <th>Sisa Tenor</th>
                                <th>:</th>
                                <td><?php echo e($data['pinjaman']->sisa_tenor); ?> dari <?php echo e($data['pinjaman']->tenor); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle">Sisa Pinjaman</th>
                                <th class="align-middle">:</th>
                                <td><input type="text" class="form-control autonumeric" onkeyup="calc_pelunasan()" data-a-dec="," data-a-sep="." name="sisa_pinjaman" id="sisa_pinjaman" value="<?php echo e($data['pinjaman']->sisa_pinjaman); ?>"></td>
                            </tr>
                            <tr>
                                <th class="align-middle">Bunga Pinjaman</th>
                                <th class="align-middle">:</th>
                                <td><input type="text" class="form-control autonumeric" onkeyup="calc_pelunasan()" data-a-dec="," data-a-sep="." name="bunga_pinjaman" id="bunga_pinjaman" value="<?php echo e($data['pinjaman']->angsuran_bunga); ?>"></td>
                            </tr>
                            <tr style="font-size:16px">
                                <th style="border-width:3px;border-color:#dedede">Total Pelunasan</th>
                                <th style="border-width:3px;border-color:#dedede">:</th>
                                <td style="font-weight:600;border-width:3px;border-color:#dedede" id="total_pelunasan">Rp <?php echo e(format_number(str_replace('-','',$data['pinjaman']->total_pelunasan))); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" value="<?php echo e($id); ?>">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <form action="<?php echo e(url('pinjaman/pengajuan/verifikasi')); ?>" id="verifikasi_transaksi" method="post">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="id" value="<?php echo e($id); ?>">
        <input type="hidden" name="status" id="status">
    </form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script src="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/pages/form-advanced.init.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/accounting.js')); ?>"></script>
    <script>
        function confirm_verifikasi(status){
            if(status==1){
                text="Apakah anda yakin ingin membatalkan verifikasi pengajuan pinjaman anggota ini?";
            }
            else if(status==2){
                text="Apakah anda yakin ingin menolak pengajuan pinjaman anggota ini?";
            }
            else if(status==3){
                text="Apakah anda yakin ingin menerima pengajuan pinjaman anggota ini?";
            }
            else if(status==4){
                text="Apakah anda yakin ingin menyelesaikan pengajuan pinjaman anggota ini?";
            }
            else if(status==5){
                text="Apakah anda yakin ingin membatalkan pengajuan pinjaman anggota ini?";
            }
            else{
                text="Apakah anda yakin ingin membatalkan pelunasan pinjaman anggota ini?";
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
                    $('#verifikasi_transaksi').submit();
                }
            });
        }

        function calc_pelunasan(){
            sisa_pinjaman = $('#sisa_pinjaman').val();
            sisa_pinjaman = sisa_pinjaman.split('.').join('');

            bunga_pinjaman = $('#bunga_pinjaman').val();
            bunga_pinjaman = bunga_pinjaman.split('.').join('');

            total_pelunasan = parseInt(sisa_pinjaman)+parseInt(bunga_pinjaman);
            $('#total_pelunasan').html('Rp '+accounting.formatNumber(total_pelunasan,0,'.',','));
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pinjaman/pengajuan/detail.blade.php ENDPATH**/ ?>