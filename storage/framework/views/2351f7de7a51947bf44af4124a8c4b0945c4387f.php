
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th colspan="8">
            <h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
            <h1 style="text-align: center;margin: 0;">LAPORAN RETUR PENJUALAN</h1>
            <p style="margin: 0;text-align: center;">
                <?php if(($request->tanggal_awal ?? '') != ''): ?>
                    <?php echo e(format_date($request->tanggal_awal)); ?>

                <?php endif; ?>
                <?php if(($request->tanggal_akhir ?? '') != ''): ?>
                    s/d <?php echo e(format_date($request->tanggal_akhir)); ?>

                <?php endif; ?>
            </p>
        </th>
    </tr>
    <tr>
        <th>No. Retur Penjualan</th>
        <th>Tanggal</th>
        <th>Barang</th>
        <th>Kategori</th>
        <th style="text-align:right">Harga</th>
        <th style="text-align:right">Jumlah</th>
        <th style="text-align:right">Subtotal</th>
        <th>Satuan</th>
    </tr>
    </thead>
    <tbody>
    <?php ($temp = ''); ?>
    <?php ($total_before = 0); ?>
    <?php ($total = 0); ?>
    <?php $__currentLoopData = $retur_penjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($temp != $value->retur_penjualan->fid_anggota): ?>
            <?php if($total_before > 0): ?>
            <tr>
                <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
                <td style="text-align: right;"><b><?php echo e(($total_before)); ?></b></td>
                <td></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="8"><b><?php echo e($value->retur_penjualan->fid_anggota ?? ''); ?> - <?php echo e($value->retur_penjualan->anggota->nama_lengkap ?? ''); ?></b></td>
            </tr>
            <?php ($total_before = 0); ?>
        <?php endif; ?>
        <tr>
            <td><?php echo e($value->retur_penjualan->no_retur ?? ''); ?></td>
            <td><?php echo e(format_date($value->retur_penjualan->created_at ?? '')); ?></td>
            <td><?php echo e($value->produk->nama_produk); ?> - <?php echo e($value->produk->kode); ?></td>
            <td><?php echo e($value->produk->kategori_produk->nama_kategori ?? ''); ?></td>
            <td style="text-align:right"><?php echo e(($value->produk->harga_jual)); ?></td>
            <td style="text-align:right"><?php echo e(($value->jumlah)); ?></td>
            <td style="text-align:right"><?php echo e(($value->produk->harga_jual * $value->jumlah)); ?></td>
            <td><?php echo e($value->produk->satuan_barang->satuan); ?></td>
        </tr>
        <?php ($temp = $value->retur_penjualan->fid_anggota); ?>
        <?php ($total_before += ($value->produk->harga_jual * $value->jumlah)); ?>
        <?php ($total += ($value->produk->harga_jual * $value->jumlah)); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
        <td style="text-align: right;"><b><?php echo e(($total_before)); ?></b></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6" style="text-align: right;"><b>Total</b></td>
        <td style="text-align: right;"><b><?php echo e(($total)); ?></b></td>
        <td></td>
    </tr>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/pos/laporan/retur_penjualan/excel.blade.php ENDPATH**/ ?>