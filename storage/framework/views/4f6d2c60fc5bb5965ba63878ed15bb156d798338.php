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
    <?php $__currentLoopData = $jurnal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="<?php echo e($value->balance == true ? '' : 'bg-danger'); ?>">
            <td><?php echo e($value->no_jurnal); ?></td>
            <td><?php echo e(format_date($value->tanggal)); ?></td>
            <td colspan="2">
                <?php echo e($value->keterangan); ?>

            </td>
            <td class="py-0 align-middle text-right">
                <a href="javascript:void(0)" onclick="info(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                <a href="javascript:void(0)" onclick="delete_data(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
            </td>
        </tr>
        <?php $__currentLoopData = $value->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td></td>
                <td></td>
                <td><?php echo e($detail->akun->kode_tampil . ' - ' . $detail->akun->nama); ?></td>
                <td class="text-right"><?php echo e(format_number($detail->debit)); ?></td>
                <td class="text-right"><?php echo e(format_number($detail->kredit)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php echo e($jurnal->links('vendor.pagination.custom')); ?>

<?php /**PATH /var/www/html/resources/views/keuangan/jurnal/_table.blade.php ENDPATH**/ ?>