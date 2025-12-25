<?php
  $app='master';
  $page='Berita dan Informasi';
  $subpage='Berita dan Informasi';
?>

<?php $__env->startSection('title'); ?>
  Berita dan Informasi |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/news.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Berita dan Informasi</h4>
          <p class="text-muted m-0">Menampilkan data berita dan informasi yang diinput oleh pengurus untuk anggota</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-9">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Berita">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <a href="<?php echo e(url('master/berita/form')); ?>" class="btn btn-primary btn-block">Tambah</a>
      </div>
    </div>
  </div>
  <?php if(count($data['berita'])==0): ?>
    <div style="width:100%;text-align:center">
      <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
      <h4 class="mt-2">Berita dan Informasi tidak Ditemukan</h4>
    </div>
  <?php else: ?>
    <div class="row mt-4">
      <?php $__currentLoopData = $data['berita']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="col-xl-4 col-sm-6">
        <div class="card">
          <div class="card-body">
            <div class="media">
              <div class="rounded produk-wrapper mr-3" style="height:100px;width:100px">
                <img src="<?php echo e((!empty($value->gambar) ? asset('storage/'.$value->gambar) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
              </div>
              <div class="media-body align-self-center">
                <h6 class="mb-2"><?php echo e($value->judul); ?></h6>
                <p class="text-muted"><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,"H:i:s")); ?></p>
              </div>
            </div>
          </div>
          <div class="px-4 py-3 border-top ">
            <div class="row">
              <div class="col-md-8">
                <span style="font-size:14px" ><?php echo e($value->jumlah_attachment); ?> Attachment</span>
              </div>
              <div class="col-md-4">
                <div class="pull-right">
                  <a href="<?php echo e(url('master/berita/form?id='.$value->id)); ?>" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                  <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                  <form action="<?php echo e(url('master/berita/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
                    <?php echo e(csrf_field()); ?>

                    <input type="hidden" name="id" value="<?php echo e($value->id); ?>">
                    <input type="hidden" name="action" value="delete">
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div style="margin-top:20px">
      <?php echo e($data['berita']->links('include.pagination', ['pagination' => $data['berita']] )); ?>

    </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>

  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/master/berita/index.blade.php ENDPATH**/ ?>