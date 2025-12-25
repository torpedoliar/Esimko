<table>
    <thead>
    <tr>
        <th colspan="11">PERIODE : <?php echo e($bulan); ?></th>
    </tr>
    <tr>
        <th rowspan="2">NO</th>
        <th rowspan="2">JENIS<br>TRANSAKSI</th>
        <th rowspan="2">NO. ANGGOTA</th>
        <th rowspan="2">NAMA<br>ANGGOTA</th>
        <th rowspan="2">NOMINAL<br>PINJAMAN</th>
        <th colspan="6">ANGSURAN</th>
        <th rowspan="2">KETERANGAN</th>
    </tr>
    <tr>
        <th>TENOR</th>
        <th>KE</th>
        <th>SISA</th>
        <th>POKOK</th>
        <th>BUNGA</th>
        <th>TOTAL</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($key+1); ?></td>
            <td><?php echo e($value->transaksi->jenis_transaksi->jenis_transaksi); ?></td>
            <td><?php echo e($value->transaksi->anggota->no_anggota ?? '-'); ?></td>
            <td><?php echo e($value->transaksi->anggota->nama_lengkap ?? '-'); ?></td>
            <td><?php echo e($value->transaksi->nominal * -1); ?></td>
            <td><?php echo e($value->transaksi->tenor); ?></td>
            <td><?php echo e($value->angsuran_ke); ?></td>
            <td><?php echo e($value->transaksi->tenor - $value->angsuran_ke); ?></td>
            <td><?php echo e(\App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_pokok)); ?></td>
            <td><?php echo e(\App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_bunga)); ?></td>
            <td><?php echo e(\App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_pokok + $value->angsuran_bunga)); ?></td>
            <td><?php echo e($value->transaksi->keterangan ?? '-'); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/pinjaman/payroll/export.blade.php ENDPATH**/ ?>