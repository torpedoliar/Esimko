<table class="table table table-bordered table-sm">
    <thead>
    <tr>
        <th>No.Jurnal</th>
        <th>Tanggal</th>
        <th>Akun</th>
        <th class="text-right">Debit</th>
        <th class="text-right">Kredit</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $jurnals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="<?php echo e($value->balance == true ? '' : 'bg-danger'); ?>">
            <td><?php echo e($value->no_jurnal); ?></td>
            <td><?php echo e(format_date($value->tanggal)); ?></td>
            <td colspan="2">
                <?php echo e($value->keterangan); ?>

            </td>
        </tr>
        <?php $__currentLoopData = $value->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td></td>
                <td></td>
                <td><?php echo e($detail->akun->kode_tampil . ' - ' . $detail->akun->nama); ?></td>
                <td class="text-right"><?php echo e(($detail->debit)); ?></td>
                <td class="text-right"><?php echo e(($detail->kredit)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/keuangan/jurnal/export.blade.php ENDPATH**/ ?>