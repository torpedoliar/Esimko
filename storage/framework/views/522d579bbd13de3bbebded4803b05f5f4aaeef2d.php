<?php
  $app='master';
  $page='Data Anggota';
  $subpage='Data Anggota';
?>

<?php $__env->startSection('title'); ?>
  Data Anggota |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <style>

  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/profile.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Data Anggota</h4>
          <p class="text-muted m-0">Menampilkan data anggota yang sudah terdaftar di koperasi</p>
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
            <option value="<?php echo e($value->color); ?>" <?php echo e(($status == $value->id ? 'selected' : '')); ?> data-id="<?php echo e($value->id); ?>" ><?php echo e($value->status_anggota); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </form>
      </div>
      <div class="col-md-7">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control box" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Anggota">
            <div class="input-group-append">
              <button class="btn btn-dark box" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-2">
        <a href="<?php echo e(url('anggota/form')); ?>" class="btn box btn-primary btn-block">Tambah Anggota</a>
      </div>
    </div>
  </div>
  <?php if(count($data['anggota'])==0): ?>
  <div style="width:100%;text-align:center">
    <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
    <h4 class="mt-2">Data Karyawan tidak Ditemukan</h4>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
          <th class="center">No. HIRS</th>
          <th>Alamat</th>
          <th>Kontak</th>
          <th class="center">Tanggal<br>Bergabung</th>
          <th class="center">Tanggal<br>Bekerja</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $data['anggota']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td onclick="location.href = '<?php echo e(url('anggota/detail?anggota='.$value->no_anggota)); ?>'" style="border-color:<?php echo e($value->color); ?>">
              <div class="media">
                <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                  <img src="<?php echo e((!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )); ?>" alt="" class="rounded-circle">
                </div>
                <div class="media-body align-self-center">
                  <p class="text-muted mb-0">No. <?php echo e($value->no_anggota); ?></p>
                  <h5 class="text-truncate font-size-13"><a href="<?php echo e(url('anggota/detail?id='.$value->id)); ?>" class="text-dark"><?php echo e($value->nama_lengkap); ?></a></h5>
                </div>
              </div>
            </td>
            <td onclick="location.href = '<?php echo e(url('anggota/detail?anggota='.$value->no_anggota)); ?>'" class="center" ><?php echo e($value->no_hirs); ?>23648736</td>
            <td onclick="location.href = '<?php echo e(url('anggota/detail?anggota='.$value->no_anggota)); ?>'"><?php echo e($value->alamat); ?></td>
            <td>
              <?php if($value->email==null && $value->no_handphone==null): ?>
                <div style="font-style:italic;white-space:nowrap;text-align:center">Belum<br>Ada Kontak</div>
              <?php else: ?>
                <div><?php echo e($value->email); ?></div>
                <div><?php echo e($value->no_handphone); ?></div>
              <?php endif; ?>
            </td>
            <td onclick="location.href = '<?php echo e(url('anggota/detail?anggota='.$value->no_anggota)); ?>'" class="center" >
              <div><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal_bergabung,'d/m/Y')); ?></div>
              <div style="font-weight:500"><?php echo e(\App\Helpers\GlobalHelper::hitung_hari($value->tanggal_bergabung,date('Y-m-d'),'y')); ?> tahun</div>
            </td>
            <td onclick="location.href = '<?php echo e(url('anggota/detail?anggota='.$value->no_anggota)); ?>'" class="center" >
              <div><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal_bekerja,'d/m/Y')); ?></div>
              <div style="font-weight:500"><?php echo e(\App\Helpers\GlobalHelper::hitung_hari($value->tanggal_bekerja,date('Y-m-d'),'y')); ?> tahun</div>
            </td>
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="<?php echo e(url('anggota/form?id='.$value->id)); ?>" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                <form action="<?php echo e(url('anggota/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
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
  <div class="mb-3">
    <?php echo e($data['anggota']->links('include.pagination', ['pagination' => $data['anggota']] )); ?>

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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/anggota/index.blade.php ENDPATH**/ ?>