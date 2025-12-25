<table class="table table-middle table-custom">
    <thead>
    <tr>
        <th>Tanggal</th>
        <th>Keterangan</th>
        <th class="text-right">Jumlah</th>
        <th class="text-right">Stok</th>
    </tr>
    </thead>
    <tbody>
    <?php ($stok = 0); ?>
    <?php $__currentLoopData = $mutasi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php ($stok += $item['jumlah']); ?>
        <tr>
            <td><?php echo e(format_date($item['tanggal'])); ?></td>
            <td><?php echo e($item['keterangan']); ?></td>
            <td class="text-right"><?php echo e($item['jumlah']); ?></td>
            <td class="text-right"><?php echo e($stok); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/manajemen_stok/barang/detail/mutasi.blade.php ENDPATH**/ ?>