<?php $__env->startSection('content'); ?>
    <style>
        .center {
            text-align: center!important;
        }
    </style>
    <h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
    <h1 style="text-align: center;margin: 0;">LAPORAN STOK PRODUK</h1>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Kode</th>
        <th>Nama Produk</th>
        <th class="center">Kategori Produk</th>
        <th class="center">Stok<br>Awal</th>
        <th class="center">Stok<br>Masuk</th>
        <th class="center">Stok<br>Keluar</th>
        <th class="center">Penyesuaian<br>Stok</th>
        <th class="center">Sisa<br>Stok</th>
        <th style="text-align:right">Harga Beli</th>
        <th style="text-align:right">Margin</th>
        <th style="text-align:right">Harga Jual</th>
    </tr>
    </thead>
    <tbody>
    <?php ($total_stok_awal = 0); ?>
    <?php ($total_stok_masuk = 0); ?>
    <?php ($total_stok_keluar = 0); ?>
    <?php ($total_stok_peny = 0); ?>
    <?php ($total_stok_sisa = 0); ?>
    <?php $__currentLoopData = $produk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($value->kode); ?></td>
            <td><?php echo e($value->nama_produk); ?></td>
            <td class="center">
                <div style="font-weight:600"><?php echo e($value->kelompok); ?></div>
                <div><?php echo e($value->kategori_produk->nama_kategori); ?></div>
                <div class="text-muted"><?php echo e($value->sub_kategori); ?></div>
            </td>
            <td class="center"><?php echo e($value->stok['stok_awal']); ?></td>
            <td class="center"><?php echo e($value->stok['pembelian'] - $value->stok['retur']); ?></td>
            <td class="center"><?php echo e($value->stok['terjual']); ?></td>
            <td class="center"><?php echo e($value->stok['penyesuaian']); ?></td>
            <td class="center"><?php echo e($value->stok['sisa']); ?></td>
            <td style="text-align:right;white-space:nowrap">Rp <?php echo e(number_format($value->harga_beli,0,',','.')); ?></td>
            <td style="text-align:right;white-space:nowrap">(<?php echo e($value->margin); ?>%) Rp <?php echo e(number_format($value->margin_nominal,0,',','.')); ?></td>
            <td style="text-align:right;white-space:nowrap">Rp <?php echo e(number_format($value->harga_jual,0,',','.')); ?></td>
        </tr>
        <?php ($total_stok_awal += $value->stok['stok_awal']); ?>
        <?php ($total_stok_masuk += ($value->stok['pembelian'] - $value->stok['retur'])); ?>
        <?php ($total_stok_keluar += ($value->stok['terjual'])); ?>
        <?php ($total_stok_peny += $value->stok['penyesuaian']); ?>
        <?php ($total_stok_sisa += $value->stok['sisa']); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <th colspan="3"><b>TOTAL</b></th>
        <th class="text-center"><?php echo e(format_number($total_stok_awal)); ?></th>
        <th class="text-center"><?php echo e(format_number($total_stok_masuk)); ?></th>
        <th class="text-center"><?php echo e(format_number($total_stok_keluar)); ?></th>
        <th class="text-center"><?php echo e(format_number($total_stok_peny)); ?></th>
        <th class="text-center"><?php echo e(format_number($total_stok_sisa)); ?></th>
        <th class="text-right"><?php echo e(format_number($produk->sum('harga_beli'))); ?></th>
        <th class="text-right"><?php echo e(format_number($produk->sum('margin_nominal'))); ?></th>
        <th class="text-right"><?php echo e(format_number($produk->sum('harga_jual'))); ?></th>
    </tr>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.report2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/laporan/produk/laporan.blade.php ENDPATH**/ ?>