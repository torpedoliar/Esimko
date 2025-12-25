<?php
  $app='master';
  $page='Pengaturan';
  $subpage='Metode Pembayaran';
?>

<?php $__env->startSection('title'); ?>
  Metode Pembayaran |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <style>
  .logo-wrapper {
    border:2px solid #e2e2e2 ;
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: none;
    margin: 0 10px 0px 0;
    transition: all .3s ease;
    background: whitesmoke;
    padding:5px
  }
  .logo-wrapper img {
    height: 100%;
    width: 100%;
    transition: all .3s ease;
    object-fit: contain;
  }

  .logo-wrapper .upload-button {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    cursor:pointer;
  }

  .logo-wrapper .file-upload{
    opacity: 0;
    pointer-events: none;
    position: absolute;
  }
  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="<?php echo e(asset('assets/images/icon-page/card-payment.png')); ?>" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Metode Pembayaran</h4>
        <p class="text-muted m-0">Menampilkan metode pembayaran dari setiap transaksi</p>
      </div>
    </div>
  </div>
  <div class="card mt-3">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-middle table-custom">
          <thead>
            <tr>
              <th></th>
              <th>Metode<br>Pembayaran</th>
              <th>Nomor<br>Rekening</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $data['rekening-pembayaran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td>
                  <div class="media">
                    <div class="logo-wrapper" style="width:50px;height:50px;background:transparent;border:none">
                      <img src="<?php echo e((!empty($value->logo) ? asset('storage/'.$value->logo) : asset('assets/images/image-default.png') )); ?>" alt="">
                    </div>
                    <div class="media-body align-self-center">
                      <h5 class="text-truncate font-size-14"><?php echo e($value->keterangan); ?></h5>
                      <p class="text-muted mb-0 mt-1 font-size-12"><?php echo e($value->metode_pembayaran); ?></p>
                    </div>
                  </div>
                </td>
                <td>
                  <?php if(!empty($value->no_rekening)): ?>
                  <h6><?php echo e($value->no_rekening); ?></h6>
                  <span><?php echo e($value->atas_nama); ?></span>
                  <?php else: ?>
                  <h6>-</h6>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>

  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pengaturan/metode_pembayaran/index.blade.php ENDPATH**/ ?>