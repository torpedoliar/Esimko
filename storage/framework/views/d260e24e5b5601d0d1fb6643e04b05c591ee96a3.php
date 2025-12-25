<table>
    <thead>
    <tr>
        <th>No. Anggota</th>
        <th>Nama Lengkap</th>
        <?php $__currentLoopData = $list_jenis_simpanan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <th style="text-align:right;">Jumlah <?php echo e(str_replace('Setoran','',$value->jenis_transaksi)); ?></th>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $anggota; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($value->no_anggota); ?></td>
            <td><?php echo e($value->nama_lengkap); ?></td>
            <?php $__currentLoopData = $list_jenis_simpanan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td style="text-align: right"><?php echo e($value->data_simpanan[$item->id] ?? 0); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/simpanan/payroll/excel.blade.php ENDPATH**/ ?>