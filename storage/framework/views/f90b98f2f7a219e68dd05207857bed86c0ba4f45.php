<?php
  $app='master';
  $page='Data Pengurus';
  $subpage='Data Pengurus';
?>

<?php $__env->startSection('title'); ?>
  Data Pengurus |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/organization-chart.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Data Pengurus</h4>
          <p class="text-muted m-0">Menampilkan data pengurus yang sudah dibentuk berdasarkan periode kepengurusan</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <form action="" method="get">
        <select class="select2 form-control" name="periode" onchange="javascript:submit()">
          <option value="new">Tambah Periode Baru</option>
          <?php $__currentLoopData = $data['pilih-periode']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($value->id); ?>" <?php echo e(($periode == $value->id ? 'selected' : '')); ?>>Periode <?php echo e($value->periode_awal); ?> s/d <?php echo e($value->periode_akhir); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        </form>
      </div>
      <div class="col-md-6">
        <form action="" method="get">
          <input type="hidden" name="periode" value="<?php echo e($periode); ?>">
          <div class="input-group">
            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Pengurus">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <a href="<?php echo e(url('master/pengurus/form')); ?>" class="btn btn-primary btn-block">Tambah Pengurus</a>
      </div>
    </div>
  </div>
  <?php if(count($data['pengurus'])==0): ?>
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
          <th class="center">Tanggal<br>Menjabat</th>
          <th class="center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $data['pengurus']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
            <td><?php echo e($value->nama_jabatan); ?></td>
            <td>
              <div><?php echo e($value->email); ?></div>
              <div><?php echo e($value->no_handphone); ?></div>
            </td>
            <td class="center"><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)); ?></td>
            <td class="center"><?php echo e($value->status); ?></td>
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="<?php echo e(url('master/pengurus/form?id='.$value->id)); ?>" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                <form action="<?php echo e(url('master/pengurus/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
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
    <?php echo e($data['pengurus']->links('include.pagination', ['pagination' => $data['pengurus']] )); ?>

  </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>

  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/master/pengurus/index.blade.php ENDPATH**/ ?>