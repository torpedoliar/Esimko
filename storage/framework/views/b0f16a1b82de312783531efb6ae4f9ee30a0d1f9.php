<?php
  $app='master';
  $page='Data Karyawan';
  $subpage='Data Karyawan';
?>

<?php $__env->startSection('title'); ?>
  Karyawan |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/meeting.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Data Karyawan</h4>
          <p class="text-muted m-0">Menampilkan data karyawan yang bekerja di koperasi</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-9">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Karyawan">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <a href="<?php echo e(url('master/karyawan/form')); ?>" class="btn btn-primary btn-block">Tambah Karyawan</a>
      </div>
    </div>
  </div>
  <?php if(count($data['karyawan'])==0): ?>
  <div style="width:100%;text-align:center">
    <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
    <h4 class="mt-2">Anggota tidak Ditemukan</h4>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-middle table-custom">
      <thead class="thead-light">
        <tr>
          <th>No. Anggota / Nama Lengkap</th>
          <th>Jabatan</th>
          <th>Contact</th>
          <th class="center">Mulai<br>Bekerja</th>
          <th class="center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $data['karyawan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td>
              <div class="media" style="border-color:<?php echo e(($value->status=='Aktif' ? '#16a085' : '#c0392b')); ?>">
                <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                  <img src="<?php echo e((!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )); ?>" alt="" class="rounded-circle">
                </div>
                <div class="media-body align-self-center">
                  <p class="text-muted mb-0">No. <?php echo e($value->no_anggota); ?></p>
                  <h5 class="text-truncate font-size-15"><a href="<?php echo e(url('anggota/detail?id='.$value->id)); ?>" class="text-dark"><?php echo e($value->nama_lengkap); ?></a></h5>
                </div>
              </div>
            </td>
            <td><?php echo e($value->jabatan); ?></td>
            <td>
              <div><?php echo e($value->email); ?></div>
              <div><?php echo e($value->no_handphone); ?></div>
            </td>
            <td class="center"><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->mulai_bekerja)); ?></td>
            <td class="center"><?php echo e($value->status); ?></td>
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="<?php echo e(url('master/karyawan/form?id='.$value->id)); ?>" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                <form action="<?php echo e(url('master/karyawan/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
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
    <?php echo e($data['karyawan']->links('include.pagination', ['pagination' => $data['karyawan']] )); ?>

  </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>

  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/master/karyawan/index.blade.php ENDPATH**/ ?>