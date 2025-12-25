


<h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
<h1 style="text-align: center;margin: 0;">LAPORAN SALDO SIMPANAN <?php echo e($request->jenis); ?></h1>
<p style="margin: 0;text-align: center;">
    <?php if(($request->tanggal_awal ?? '') != ''): ?>
        <?php echo e(format_date($request->tanggal_awal)); ?>

    <?php endif; ?>
    <?php if(($request->tanggal_akhir ?? '') != ''): ?>
        s/d <?php echo e(format_date($request->tanggal_akhir)); ?>

    <?php endif; ?>
</p>
<br>
<table class="table table-middle table-custom">
    <thead>
    <tr>
        <th>Tanggal</th>
        <th>No. Anggota</th>
        <th>Anggota</th>
        <th style="text-align:right;width:150px">Nominal</th>
        <th>Status</th>
        <th>Operator</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e(format_date($value->tanggal)); ?></td>
            <td><?php echo e($value->anggota->no_anggota); ?></td>
            <td><?php echo e($value->anggota->nama_lengkap); ?></td>
            <td style="text-align:right">Rp <?php echo e(format_number($value->nominal)); ?></td>
            <td><?php echo e($value->status->status); ?></td>
            <td><?php echo e($value->operator->nama_lengkap); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php /**PATH /var/www/html/resources/views/monitoring/saldo_simpanan_excel.blade.php ENDPATH**/ ?>