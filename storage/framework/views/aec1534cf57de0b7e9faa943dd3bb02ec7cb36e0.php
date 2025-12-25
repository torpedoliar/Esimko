
<h2># Pembelian</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No.Pembelian</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $pembelian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($value->pembelian->no_pembelian); ?></td>
            <td><?php echo e($value->pembelian->tanggal); ?></td>
            <td class="center"><?php echo e($value->jumlah); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<h2 class="mt-3"># Retur Pembelian</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No. Retur Pembelian</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $retur_pembelian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($value->retur_pembelian->no_retur); ?></td>
            <td><?php echo e($value->retur_pembelian->tanggal); ?></td>
            <td class="center"><?php echo e($value->jumlah); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<h2># Penjualan</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No.Penjualan</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $penjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($value->penjualan->no_transaksi); ?></td>
            <td><?php echo e($value->penjualan->tanggal); ?></td>
            <td class="center"><?php echo e($value->jumlah); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<h2 class="mt-3"># Retur Penjualan</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No. Retur Penjualan</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $retur_penjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($value->retur_penjualan->no_retur); ?></td>
            <td><?php echo e($value->retur_penjualan->tanggal); ?></td>
            <td class="center"><?php echo e($value->jumlah); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<h2 class="mt-3"># Penyesuaian Stok</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Keterangan</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $opname; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($value->keterangan); ?></td>
            <td><?php echo e($value->tanggal); ?></td>
            <td class="center"><?php echo e($value->jumlah); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php /**PATH /var/www/html/resources/views/pos/laporan/mutasi/excel.blade.php ENDPATH**/ ?>