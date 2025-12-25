<?php $__env->startSection('css'); ?>
  <style>
  .label-container {
    display: flex;
    flex-wrap: wrap;
  }

  .label-container .label {
    border:1px solid #363636;
    border-top:8px solid #363636;
    border-bottom:8px solid #363636;
    width: 300px;
    margin: 4px;
  }
  .label-container .label .title{
    padding:8px;
    border-bottom: 1px solid #363636;
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
    white-space: normal;
    -webkit-line-clamp: 2;
    height:32px;
    line-height:18px;
  }
  .label-container .label .price{
    padding:10px;
    border-bottom: 1px solid #363636;
    font-size:45px;
    font-weight:600;
    text-align:right;
    position: relative;
  }
  .label-container .label .footer{
    height:55px;
    position: relative;
  }
  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
  <div class="label-container">
    <?php $__currentLoopData = $data['produk']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php for($i=1; $i <= $value->jumlah ; $i++): ?>
        <div class="label">
          <div class="title"><?php echo e($value->nama_produk); ?></div>
          <div class="price">
            <div style="position:absolute;font-size:20px;top:5px;left:5px;font-weight:400">Rp</div>
            <?php echo e(number_format($value->harga_jual,0,',','.')); ?>

            <div style="position:absolute;font-size:14px;bottom:5px;left:5px;font-weight:300"><?php echo e($value->kode); ?></div>
          </div>
          <div class="footer">
            <div style="position:absolute;font-size:12px;top:5px;left:5px;font-weight:300"><?php echo e($value->satuan); ?></div>
            <div style="position:absolute;font-size:12px;top:5px;right:5px;font-weight:300"><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?></div>
            <div style="position:absolute;font-size:12px;bottom:5px;right:5px;font-weight:300">
              <?php echo e((!empty($value->sub_kategori) ? $value->sub_kategori : $value->kategori)); ?>

            </div>
          </div>
        </div>
      <?php endfor; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.report', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/manajemen_stok/cetak/label_harga/cetak.blade.php ENDPATH**/ ?>