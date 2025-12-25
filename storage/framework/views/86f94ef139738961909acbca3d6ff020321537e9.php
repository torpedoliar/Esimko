<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Akun</th>
        <th class="text-right">Nominal</th>
        <th></th>
        <th>Akun</th>
        <th class="text-right">Nominal</th>
    </tr>
    </thead>
    <tbody>
    <?php ($labarugi = false); ?>
    <?php $__currentLoopData = $list_akun; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php for($i = 3; $i < strlen($value->kode); $i++): ?> &nbsp; <?php endfor; ?> <?php echo e($value->kode_tampil . ' - ' . $value->nama); ?></td>
            <td class="text-right"><?php echo e(format_number($value->nominal)); ?></td>
            <td>&nbsp;</td>
            <?php ($value2 = $list_akun2[$key] ?? []); ?>
            <?php if(!empty($value2)): ?>
                <td><?php for($i = 3; $i < strlen($value2->kode); $i++): ?> &nbsp; <?php endfor; ?> <?php echo e($value2->kode_tampil . ' - ' . $value2->nama); ?></td>
                <td class="text-right"><?php echo e(($value2->nominal * -1)); ?></td>
            <?php endif; ?>
            <?php if(empty($value2) && $labarugi === false): ?>
                <?php ($labarugi = true); ?>
                <td>Laba Rugi</td>
                <td class="text-right"><?php echo e(format_number($laba_rugi)); ?></td>
            <?php endif; ?>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
    <tfoot>
    <tr>
        <td class="font-weight-bold">Total</td>
        <td class="font-weight-bold text-right"><?php echo e(($list_akun->sum('nominal'))); ?></td>
        <td></td>
        <td class="font-weight-bold">Total</td>
        <td class="font-weight-bold text-right"><?php echo e((($list_akun2->sum('nominal') * -1) + $laba_rugi)); ?></td>
    </tr>
    </tfoot>
</table>
<?php /**PATH /var/www/html/resources/views/keuangan/neraca/export.blade.php ENDPATH**/ ?>