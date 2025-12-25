<?php
  $app='sinjam';
  $page='Penarikan Simpanan';
  $subpage='Semua Simpanan';
?>

<?php $__env->startSection('title'); ?>
  Semua Simpanan |
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
        <h4 class="mb-0 font-size-18">Penarikan Semua Simpanan</h4>
        <p class="text-muted m-0">Menampilkan detail penarikan semua simpanan yang sudah diinput oleh petugas atau anggota</p>
      </div>
    </div>
  </div>
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
      <div class="alert alert-secondary" role="alert">
        <h5 class="mb-2">Verifikasi Transaksi</h5>
        <?php if($data['simpanan']->fid_status==1): ?>
          <p>Harap segera melakukan verifikasi terhadap permintaan penarikan simpanan sukarela</p>
        <?php else: ?>
          <p>Terimakasih sudah melakukan verifikasi terhadap transaksi ini, Silahkan menunggu anggota berkunjung ke koperasi untuk melakikan konfirmasi penarikan simpanan</p>
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
    </div>
    <div class="card-footer">
      <a class="btn btn-dark pull-right" href="<?php echo e(url('penarikan/penutupan')); ?>" >Kembali</a>
    </div>
  </div>
</div>
<form action="<?php echo e(url('penarikan/penutupan/verifikasi')); ?>" id="verifikasi_transaksi" method="post">
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/penarikan/penutupan/detail.blade.php ENDPATH**/ ?>