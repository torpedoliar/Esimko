<table class="table table-middle table-custom">
    <thead>
    <tr>
        <th>No. Anggota</th>
        <th>Nama Lengkap</th>
        <th style="text-align:right;width:150px">Simpanan<br>Pokok</th>
        <th style="text-align:right;width:150px">Simpanan<br>Wajib</th>
        <th style="text-align:right;width:150px">Simpanan<br>Sukarela</th>
        <th style="text-align:right;width:150px">Simpanan<br>Hari Raya</th>
        <th style="text-align:right;width:150px">Total Saldo</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            
            <td><?php echo e($value->no_anggota); ?></td>
            <td><?php echo e($value->nama_lengkap); ?></td>
            <td style="text-align:right"><?php echo e($value->simpanan_pokok); ?></td>
            <td style="text-align:right"><?php echo e($value->simpanan_wajib); ?></td>
            <td style="text-align:right"><?php echo e(round($value->simpanan_sukarela, 0)); ?></td>
            <td style="text-align:right"><?php echo e($value->simpanan_hari_raya); ?></td>
            <td style="text-align:right"><?php echo e(round($value->total_simpanan)); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/monitoring/saldo_simpanan_cetak.blade.php ENDPATH**/ ?>