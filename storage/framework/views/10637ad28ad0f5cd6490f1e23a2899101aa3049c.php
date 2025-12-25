<div class="card">
  <div class="card-header">
    <h5>Riwayat Gaji Pokok</h5>
  </div>
  <div class="card-body">
    <?php if(count($data['gaji-pokok'])==0): ?>

    <?php else: ?>
    <table class="table table-bordered table-middle mb-0">
      <thead>
        <tr class="thead-light">
          <th class="center">Bulan</th>
          <th style="text-align:right">Gaji Pokok</th>
          <th class="center">Slip Gaji</th>
          <th>Created By</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $data['gaji-pokok']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr >
            <td class="center" ><?php echo e($value->bulan); ?></td>
            <td style="text-align:right">Rp <?php echo e(number_format($value->gaji_pokok,'0',',','.')); ?></td>
            <td></td>
            <td>
              <h6><?php echo e($value->nama_lengkap); ?></h6>
              at <?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?> <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')); ?>

            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
<?php /**PATH /var/www/html/resources/views/profil/gaji_pokok.blade.php ENDPATH**/ ?>