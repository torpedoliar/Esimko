<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th colspan="5"><?php echo e($akun->kode_tampil . ' - ' . $akun->nama); ?></th>
    </tr>
    <tr>
        <th>Tanggal</th>
        <th>Keterangan</th>
        <th>Debit</th>
        <th>Kredit</th>
        <th>Saldo</th>
    </tr>
    </thead>
    <tbody>
    <?php ($saldo = 0); ?>
    <?php $__currentLoopData = $jurnals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jurnal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php ($saldo += ($jurnal->nominal)); ?>
        <tr>
            <td><?php echo e(format_date($jurnal->jurnal->tanggal)); ?></td>
            <td><?php echo e($jurnal->jurnal->keterangan); ?></td>
            <td class="text-right"><?php echo e(($jurnal->debit)); ?></td>
            <td class="text-right"><?php echo e(($jurnal->kredit)); ?></td>
            <td class="text-right"><?php echo e(($saldo)); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/keuangan/buku_besar/export.blade.php ENDPATH**/ ?>