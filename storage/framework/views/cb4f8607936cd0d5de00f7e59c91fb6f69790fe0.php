<?php
  $app='sinjam';
  $page='Penarikan Simpanan';
  $subpage='Simpanan Sukarela';
?>

<?php $__env->startSection('title'); ?>
  Penarikan |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/penarikan.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Penarikan Simpanan Sukarela</h4>
          <p class="text-muted m-0">Menampilkan data penarikan simpanan sukarela yang sudah diinput oleh petugas atau anggota</p>
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
      <div class="col-md-6">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Simpanan Sukarela">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <a href="<?php echo e(url('penarikan/sukarela/form')); ?>" class="btn btn-primary btn-block">Formulir Penarikan</a>
      </div>
    </div>
  </div>
  <?php if(count($data['penarikan'])==0): ?>
  <div style="width:100%;text-align:center">
    <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
    <h4 class="mt-3">Data Penarikan Simpanan Sukarela<BR>Tidak Ditemukan</h4>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th class="center">Tanggal</th>
          <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
          <th class="center">Metode<br>Penarikan</th>
          <th style="text-align:right">Jumlah<br>Penarikan</th>
          <th class="center">Keterangan</th>
          <th>Created by</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $data['penarikan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td class="center" style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')); ?></td>
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
            <td class="center"><?php echo e($value->metode_transaksi); ?></td>
            <td style="text-align:right">Rp <?php echo e(number_format(str_replace('-','',$value->nominal),0,',','.')); ?></td>
            <td class="center"><?php echo e((!empty($value->keterangan) ? $value->keterangan : 'Tidak ada Keterangan')); ?></td>
            <td style="width:1px;white-space:nowrap">
              <h6>(<?php echo e($value->created_by); ?>) <?php echo e($value->nama_petugas); ?></h6>
              at <?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')); ?>

            </td>
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="<?php echo e(url('penarikan/sukarela/detail?id='.$value->id)); ?>" class="text-dark"><i class="bx bx-search h3 m-0"></i></a>
              </div>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>
  <div class="mb-3">
    <?php echo e($data['penarikan']->links('include.pagination', ['pagination' => $data['penarikan']] )); ?>

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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/penarikan/sukarela/index.blade.php ENDPATH**/ ?>