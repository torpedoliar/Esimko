
    <h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
    <h1 style="text-align: center;margin: 0;">LAPORAN STOK PRODUK</h1>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Kode Produk</th>
        <th>Nama Produk</th>
        <th class="center">Kategori Produk</th>
        <th class="center">Stok Awal</th>
        <th class="center">Stok Masuk</th>
        <th class="center">Stok Keluar</th>
        <th class="center">Sisa Penyesuaian</th>
        <th class="center">Sisa Stok</th>
        <th class="center">Satuan</th>
        <th style="text-align:right">Harga Beli</th>
        <th style="text-align:right">Margin %</th>
        <th style="text-align:right">Margin Rp.</th>
        <th style="text-align:right">Harga Jual</th>
    </tr>
    </thead>
    <tbody>
    <?php ($total_stok_awal = 0); ?>
    <?php ($total_stok_masuk = 0); ?>
    <?php ($total_stok_keluar = 0); ?>
    <?php ($total_stok_peny = 0); ?>
    <?php ($total_stok_sisa = 0); ?>
    <?php ($total_harga_beli = 0); ?>
    <?php $__currentLoopData = $produk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td>'<?php echo e($value['kode'] ?? ''); ?></td>
            <td><?php echo e($value['nama_produk'] ?? ''); ?></td>
            <td><?php echo e($value['kategori_produk']['nama_kategori']); ?></td>
            <td class="center"><?php echo e($value['stok']['stok_awal']); ?></td>
            <td class="center"><?php echo e($value['stok']['pembelian'] - $value['stok']['retur']); ?></td>
            <td class="center"><?php echo e($value['stok']['terjual']); ?></td>
            <td class="center"><?php echo e($value['stok']['penyesuaian']); ?></td>
            <td class="center"><?php echo e($value['stok']['sisa']); ?></td>
            <td><?php echo e($value['satuan_barang']['satuan']); ?></td>
            <td style="text-align:right;white-space:nowrap"><?php echo e($value['harga_beli']); ?></td>
            <td style="text-align:right;white-space:nowrap"><?php echo e($value['margin']); ?>%</td>
            <td><?php echo e($value['margin_nominal']); ?></td>
            <td style="text-align:right;white-space:nowrap"><?php echo e($value['harga_jual']); ?></td>
        </tr>
        <?php ($total_stok_awal += $value['stok']['stok_awal']); ?>
        <?php ($total_stok_masuk += ($value['stok']['pembelian'] - $value['stok']['retur'])); ?>
        <?php ($total_stok_keluar += ($value['stok']['terjual'])); ?>
        <?php ($total_stok_peny += $value['stok']['penyesuaian']); ?>
        <?php ($total_stok_sisa += $value['stok']['sisa']); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <th colspan="3"><b>TOTAL</b></th>
        <th class="text-left"><?php echo e(($total_stok_awal)); ?></th>
        <th class="text-left"><?php echo e(($total_stok_masuk)); ?></th>
        <th class="text-left"><?php echo e(($total_stok_keluar)); ?></th>
        <th class="text-left"><?php echo e(($total_stok_peny)); ?></th>
        <th class="text-left"><?php echo e(($total_stok_sisa)); ?></th>
        <th></th>
        <th class="text-right"><?php echo e(array_sum(array_column($produk, 'harga_beli'))); ?></th>
        <th></th>
        <th class="text-right"><?php echo e(array_sum(array_column($produk, 'margin_nominal'))); ?></th>
        <th class="text-right"><?php echo e(array_sum(array_column($produk, 'harga_jual'))); ?></th>
    </tr>
    </tbody>
</table>

<?php /**PATH /var/www/html/resources/views/pos/laporan/produk/excel.blade.php ENDPATH**/ ?>