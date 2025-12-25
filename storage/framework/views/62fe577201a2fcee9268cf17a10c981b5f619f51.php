<?php
  $page='Berita';
  $subpage='Berita';
?>

<?php $__env->startSection('title'); ?>
  Berita dan Informasi |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="content-breadcrumb mb-2">
  <div class="container-fluid">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/news.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Berita dan Informasi</h4>
          <p class="text-muted m-0">Menampilkan data berita dan informasi yang diinput oleh pengurus untuk anggota</p>
        </div>
      </div>
    </div>
    <form action="" method="get">
      <div class="input-group">
        <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Berita">
        <div class="input-group-append">
          <button class="btn btn-dark" type="submit">Search</button>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="container-fluid">
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
            <a href="<?php echo e(url('main/berita/detail?id='.$value->id)); ?>">
              <div class="media">
                <div class="rounded produk-wrapper mr-3 m-0" style="height:100px;width:100px">
                  <img src="<?php echo e((!empty($value->gambar) ? asset('storage/'.$value->gambar) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                </div>
                <div class="media-body align-self-center">
                  <h6 class="mb-2"><?php echo e($value->judul); ?></h6>
                  <p class="text-muted"><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,"H:i:s")); ?></p>
                </div>
              </div>
            </a>
          </div>
          <div class="px-4 py-3 border-top ">
            <div class="pull-right">
              <span style="font-size:14px" ><?php echo e($value->jumlah_attachment); ?> Attachment</span>
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

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/main/berita/index.blade.php ENDPATH**/ ?>