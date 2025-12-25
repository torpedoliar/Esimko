
    <h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
    <h1 style="text-align: center;margin: 0;">LAPORAN PENYESUAIAN STOK</h1>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Tanggal</th>
        <th>Kode Barang</th>
        <th>Nama Barang</th>
        <th class="center">Jumlah</th>
        <th class="center">Jumlah * Harga</th>
        <th class="center">Jenis</th>
        <th>Keterangan</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $penyesuaian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')); ?></td>
            <td><?php echo e($value->produk->kode ?? ''); ?></td>
            <td><?php echo e($value->produk->nama_produk ?? ''); ?></td>
            <td class="center"><?php echo e($value->jumlah); ?></td>
            <td class="center"><?php echo e($value->jumlah * $value->hpp); ?></td>
            <td class="center"><?php echo e($value->jenis); ?></td>
            <td><?php echo e($value->keterangan); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/pos/laporan/penyesuaian/excel.blade.php ENDPATH**/ ?>