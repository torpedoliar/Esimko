<?php
  $app='sinjam';
  $page='Monitoring Anggota';
  $subpage='Sisa Pinjaman';
?>

<?php $__env->startSection('title'); ?>
  Monitoring Sisa Pinjaman |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/save-money.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Monitoring Sisa Pinjaman</h4>
          <p class="text-muted m-0">Menampilkan data sisa pinjaman anggota</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4">
        <form action="" method="get" id="status_form" >
          <input type="hidden" value="<?php echo e($status); ?>" id="status_id" name="status" value="">
          <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
            <option value="#282828" data-id="all">Semua Status</option>
            <?php $__currentLoopData = $data['status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value->color); ?>" <?php echo e(($status == $value->id ? 'selected' : '')); ?> data-id="<?php echo e($value->id); ?>" ><?php echo e($value->status_anggota); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </form>
      </div>
      <div class="col-md-8">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Anggota">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php if($data['sisa_pinjaman']==null || count($data['sisa_pinjaman']) == 0): ?>
  <div style="width:100%;text-align:center">
    <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
    <h4 class="mt-3">Anggota tidak Ditemukan</h4>
  </div>
  <?php else: ?>
  <div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
          <th style="text-align:right;width:150px">Sisa Pinjaman<br>Jangka Panjang</th>
          <th style="text-align:right;width:150px">Sisa Pinjaman<br>Jangka Pendek</th>
          <th style="text-align:right;width:150px">Sisa Pinjaman<br>Barang</th>
          <th style="text-align:right;width:150px">Total<br>Sisa Pinjaman</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $data['sisa_pinjaman']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td style="border-color:<?php echo e($value->color); ?>">
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
            <td style="text-align:right">
              <div style="font-weight:500">Rp <?php echo e(number_format($value->sisa_jangka_panjang,'0',',','.')); ?></div>
              <?php if($value->sisa_jangka_panjang!=0): ?><div><?php echo e($value->tenor_jangka_panjang['sisa']); ?> dari <?php echo e($value->tenor_jangka_panjang['tenor']); ?></div><?php endif; ?>
            </td>
            <td style="text-align:right">
              <div style="font-weight:500">Rp <?php echo e(number_format($value->sisa_jangka_pendek,'0',',','.')); ?></div>
              <?php if($value->sisa_jangka_pendek!=0): ?><div><?php echo e($value->tenor_jangka_pendek['sisa']); ?> dari <?php echo e($value->tenor_jangka_pendek['tenor']); ?></div><?php endif; ?>
            </td>
            <td style="text-align:right">
              <div style="font-weight:500">Rp <?php echo e(number_format($value->sisa_barang,'0',',','.')); ?></div>
              <?php if($value->sisa_barang!=0): ?><div><?php echo e($value->tenor_barang['sisa']); ?> dari <?php echo e($value->tenor_barang['tenor']); ?></div><?php endif; ?>
            </td>
            <td style="text-align:right">
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>
  <div class="mb-4 mt-3">
    <?php echo e($data['sisa_pinjaman']->links('include.pagination', ['pagination' => $data['sisa_pinjaman']] )); ?>

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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/monitoring/sisa_pinjaman.blade.php ENDPATH**/ ?>