<?php
  $app='sinjam';
  $page='Penarikan Simpanan';
  $subpage='Simpanan Sukarela';
?>

<?php $__env->startSection('title'); ?>
  Penarikan |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <style>
  .list-anggota{
    padding-bottom:10px;
    border-bottom: 1px solid #f2f2f2;
    margin-top:10px;
    cursor: pointer;
  }
  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="<?php echo e(asset('assets/images/icon-page/penarikan.png')); ?>" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Penarikan Simpanan Sukarela</h4>
        <p class="text-muted m-0">Menampilkan detail penarikan simpanan sukarela yang sudah diinput oleh petugas atau anggota</p>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <div class="center mb-5">
            <img src="<?php echo e(asset('assets/images/'.$data['simpanan']->icon)); ?>" style="width:80px">
            <h4 class="mt-3"><?php echo e($data['keterangan']->label); ?></h4>
            <p><?php echo e($data['keterangan']->keterangan); ?></p>
          </div>
          <h5 class="mb-3">Informasi Transaksi</h5>
          <table class="table table-informasi">
            <tr>
              <th width="180px">No. Anggota</th>
              <th width="10px">:</th>
              <td><?php echo e($data['simpanan']->no_anggota); ?></td>
            </tr>
            <tr>
              <th>Nama Lengkap</th>
              <th>:</th>
              <td><?php echo e($data['simpanan']->nama_lengkap); ?></td>
            </tr>
            <tr>
              <th>Jenis Transaksi</th>
              <th>:</th>
              <td><?php echo e($data['simpanan']->jenis_transaksi); ?></td>
            </tr>
            <tr>
              <th>Metode Transaksi</th>
              <th>:</th>
              <td><?php echo e($data['simpanan']->metode_transaksi); ?></td>
            </tr>
            <tr>
              <th>Jumlah Simpanan</th>
              <th>:</th>
              <td>Rp <?php echo e(number_format(str_replace('-','',$data['simpanan']->nominal),0,',','.')); ?></td>
            </tr>
            <tr>
              <th>Keterangan</th>
              <th>:</th>
              <td><?php echo e((!empty($data['simpanan']->keterangan) ? $data['simpanan']->keterangan : 'Tidak ada keterangan')); ?></td>
            </tr>
          </table>
          <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
          <ul class="verti-timeline list-unstyled">
            <li class="event-list">
              <div class="event-timeline-dot">
                <i class="bx bx-right-arrow-circle"></i>
              </div>
              <h6><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($data['simpanan']->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($data['simpanan']->created_at,'H:i:s')); ?></h6>
              <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500"><?php echo e($data['simpanan']->nama_petugas); ?></span></p>
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
        <div class="card-footer">
          <div class="pull-right">
            <a class="btn btn-dark" href="<?php echo e(url('penarikan/sukarela')); ?>" >Kembali</a>
          </div>
        </div>
      </div>
    </div>
    <?php if($data['simpanan']->fid_status!=5): ?>
    <div class="col-md-4">
      <div style="position:sticky;top:100px;width:100%;z-index:100">
        <div class="alert alert-secondary mb-4" role="alert">
          <h5 class="font-size-18 mb-3">Verifikasi Transaksi</h5>
          <?php if($data['simpanan']->fid_status==1): ?>
            <p class="font-size-15">Harap segera melakukan verifikasi terhadap permintaan penarikan simpanan sukarela</p>
          <?php elseif($data['simpanan']->fid_status==2): ?>
            <p class="font-size-15">Terimakasih sudah melakukan verifikasi terhadap transaksi ini, Silahkan batalkan verifikasi jika ingin mengubah keputusan verifikasi</p>
          <?php elseif($data['simpanan']->fid_status==3): ?>
            <p class="font-size-15">Terimakasih sudah melakukan verifikasi terhadap transaksi ini, Silahkan menunggu anggota berkunjung ke koperasi untuk melakikan konfirmasi penarikan simpanan</p>
          <?php else: ?>
            <p class="font-size-15">Terimakasih sudah menyelesaikan proses permintaan penarikan simpanan sukarela</p>
          <?php endif; ?>
          <?php if($data['simpanan']->fid_status==1): ?>
          <button class="btn btn-danger" onclick="confirm_verifikasi(2)">Ditolak</button>
          <button class="btn btn-info" onclick="confirm_verifikasi(3)">Disetujui</button>
          <?php elseif($data['simpanan']->fid_status==3): ?>
          <button class="btn btn-dark" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
          <button class="btn btn-primary" onclick="confirm_verifikasi(4)">Selesai</button>
          <?php else: ?>
          <button class="btn btn-dark" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
          <?php endif; ?>
        </div>
        <?php if($data['simpanan']->fid_status <= 2 ): ?>
        <div class="alert alert-danger  mb-4" role="alert">
          <h5 class="font-size-18">Batalkan Transaksi</h5>
          <p class="mt-3">Silahkan edit atau batalkan transaksi sebelum diverifikasi oleh petugas</p>
          <button class="btn btn-danger" onclick="confirm_verifikasi(5)">Batalkan Transaksi</button>
          <a class="btn btn-secondary"  href="<?php echo e(url('penarikan/sukarela/form?id='.$id)); ?>">Edit Transaksi</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<form action="<?php echo e(url('penarikan/sukarela/verifikasi')); ?>" id="verifikasi_transaksi" method="post">
  <?php echo e(csrf_field()); ?>

  <input type="hidden" name="id" value="<?php echo e($id); ?>">
  <input type="hidden" name="status" id="status">
</form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
<script>
function confirm_verifikasi(status){
  if(status==2){
    text="Apakah anda yakin ingin menolak transaksi penarikan simpanan ini?";
  }
  else if(status==3){
    text="Apakah anda yakin ingin menerima transaksi penarikan simpanan ini?";
  }
  else if(status==4){
    text="Apakah anda yakin ingin menyelesaikan proses transaksi penarikan simpanan ini?";
  }
  else if(status==5){
    text="Apakah anda yakin ingin membatalkan transaksi penarikan simpanan ini?";
  }
  else{
    text="Apakah anda yakin ingin membatalkan verifikasi transaksi penarikan simpanan ini?";
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
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/penarikan/sukarela/detail.blade.php ENDPATH**/ ?>