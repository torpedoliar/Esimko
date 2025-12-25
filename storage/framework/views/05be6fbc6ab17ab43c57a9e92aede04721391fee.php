<table class="table table-sm table-bordered">
    <thead>
    <tr>
        <th>Akun</th>
        <th class="text-right">Nominal</th>
    </tr>
    </thead>
    <tbody>
    <?php ($laba = 0); ?>
    <?php ($pendapatan = 0); ?>
    <?php ($pengeluaran = 0); ?>
    <?php $__currentLoopData = $list_akun; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($value->kode_tampil == '4'): ?>
            <tr class="border-top">
                <td><b>Total Pendapatan</b></td>
                <td class="text-right"><?php echo e(format_number($pendapatan)); ?></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
        <?php endif; ?>

        <?php ($laba += ($value->nominal)); ?>
        <?php if(substr($value->kode_tampil, 0, 1) == '3'): ?>
            <?php ($pendapatan += (-1 * $value->nominal)); ?>
        <?php endif; ?>
        <?php if(substr($value->kode_tampil, 0, 1) == '4'): ?>
            <?php ($pengeluaran += ($value->nominal)); ?>
        <?php endif; ?>
        <tr>
            <td><?php for($i = 3; $i < strlen($value->kode); $i++): ?> &nbsp; <?php endfor; ?> <?php echo e($value->kode_tampil . ' - ' . $value->nama); ?></td>
            <td class="text-right"><?php echo e(format_number(substr($value->kode_tampil, 0, 1) == '3' ? (-1 * $value->nominal) : $value->nominal)); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr class="border-top">
        <td><b>Total Pengeluaran</b></td>
        <td class="text-right"><?php echo e(format_number($pengeluaran)); ?></td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr class="border-top">
        <td><b>Laba/rugi</b></td>
        <td class="text-right"><?php echo e(format_number($pendapatan - $pengeluaran)); ?></td>
    </tr>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/keuangan/laba_rugi/_table.blade.php ENDPATH**/ ?>