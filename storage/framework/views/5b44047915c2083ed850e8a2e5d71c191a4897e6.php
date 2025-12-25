<div class="d-flex flex-row justify-content-between" style="gap: 20px;">
    <div class="" style="flex-grow: 1">
        <table class="table table-bordered table-sm">
            <thead>
            <tr>
                <th>Akun</th>
                <th class="text-right">Nominal</th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $list_akun; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php for($i = 3; $i < strlen($value->kode); $i++): ?> &nbsp; <?php endfor; ?> <?php echo e($value->kode_tampil . ' - ' . $value->nama); ?></td>
                    <td class="text-right"><?php echo e(format_number($value->nominal)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
            <tr>
                <td class="font-weight-bold">Total</td>
                <td class="font-weight-bold text-right"><?php echo e(format_number($list_akun->sum('nominal'))); ?></td>
            </tr>
            </tfoot>
        </table>
    </div>
    <div class="" style="flex-grow: 1">
        <table class="table table-bordered table-sm">
            <thead>
            <tr>
                <th>Akun</th>
                <th class="text-right">Nominal</th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $list_akun2; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php for($i = 3; $i < strlen($value->kode); $i++): ?> &nbsp; <?php endfor; ?> <?php echo e($value->kode_tampil . ' - ' . $value->nama); ?></td>
                    <td class="text-right"><?php echo e(format_number($value->nominal * -1)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>Laba Rugi</td>
                <td class="text-right"><?php echo e(format_number($laba_rugi)); ?></td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <td class="font-weight-bold">Total</td>
                <td class="font-weight-bold text-right"><?php echo e(format_number(($list_akun2->sum('nominal') * -1) + $laba_rugi)); ?></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/keuangan/neraca/_table.blade.php ENDPATH**/ ?>