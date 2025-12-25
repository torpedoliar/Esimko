<?php $__env->startSection('content'); ?>
<h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
<h1 style="text-align: center;margin: 0;">LAPORAN RETUR PEMBELIAN</h1>
<p style="margin: 0;text-align: center;">
    <?php if(($request->tanggal_awal ?? '') != ''): ?>
        <?php echo e(format_date($request->tanggal_awal)); ?>

    <?php endif; ?>
    <?php if(($request->tanggal_akhir ?? '') != ''): ?>
        s/d <?php echo e(format_date($request->tanggal_akhir)); ?>

    <?php endif; ?>
</p>
<br>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No. Retur Pembelian</th>
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
    <?php $__currentLoopData = $retur_pembelian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($temp != $value->retur_pembelian->fid_supplier): ?>
            <?php if($total_before > 0): ?>
                <tr>
                    <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
                    <td style="text-align: right;"><b><?php echo e(($total_before)); ?></b></td>
                    <td></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td colspan="8"><b><?php echo e($value->retur_pembelian->supplier->nama_supplier ?? ''); ?></b></td>
            </tr>
            <?php ($total_before = 0); ?>
        <?php endif; ?>
        <tr>
            <td><?php echo e($value->retur_pembelian->no_retur ?? ''); ?></td>
            <td><?php echo e(format_date($value->retur_pembelian->created_at ?? '')); ?></td>
            <td><?php echo e($value->produk->nama_produk); ?> - <?php echo e($value->produk->kode); ?></td>
            <td><?php echo e($value->produk->kategori_produk->nama_kategori ?? ''); ?></td>
            <td style="text-align:right"><?php echo e(($value->harga)); ?></td>
            <td style="text-align:right"><?php echo e(($value->jumlah)); ?></td>
            <td style="text-align:right"><?php echo e(($value->total)); ?></td>
            <td><?php echo e($value->produk->satuan_barang->satuan); ?></td>
        </tr>
        <?php ($temp = $value->retur_pembelian->fid_supplier); ?>
        <?php ($total_before += $value->total); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
        <td style="text-align: right;"><b><?php echo e(($total_before)); ?></b></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6" style="text-align: right;"><b>Total</b></td>
        <td style="text-align: right;"><b><?php echo e(($retur_pembelian->sum('total'))); ?></b></td>
        <td></td>
    </tr>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.report2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/laporan/retur_pembelian/laporan.blade.php ENDPATH**/ ?>