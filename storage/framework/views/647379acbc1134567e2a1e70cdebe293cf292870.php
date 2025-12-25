<?php
  $page='Berita';
  $subpage='Berita';
?>

<?php $__env->startSection('title'); ?>
  Berita dan Informasi |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <style>
  .content p{
    font-size: 15px;
    font-weight:300;
  }
  .list-berita{
    padding:20px 0px;
    border-bottom: 1px solid #e6e6e6;
    display: block
  }
  .list-berita:hover h6{
    color:#429d9c
  }
  .list-berita .produk-wrapper{
    margin:0px
  }
  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
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
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h3 class="mb-3"><?php echo e($data['berita']->judul); ?></h3>
          <img src="<?php echo e((!empty($data['berita']->gambar) ? asset('storage/'.$data['berita']->gambar) : asset('assets/images/produk-default.jpg'))); ?>" style="width:100%" />
          <div class="content mt-3"><?php echo $data['berita']->content; ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <h5>Attachment Berita</h5>
      <?php $__currentLoopData = $data['attachment']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <a class="list-berita" href="<?php echo e(asset('storage/'.$value->attachment)); ?>">
        <div class="media">
          <img src="<?php echo e(asset('assets/images/file.png')); ?>" style="width:60px" class="mr-2">
          <div class="media-body align-self-center">
            <h6 style="font-size:14px;font-weight:400"><?php echo e($value->judul); ?></h6>
          </div>
        </div>
      </a>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>

  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/main/berita/detail.blade.php ENDPATH**/ ?>