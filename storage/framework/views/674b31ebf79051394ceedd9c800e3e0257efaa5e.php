<?php $__env->startSection('content'); ?>
<h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
<h1 style="text-align: center;margin: 0;">LAPORAN PEMBELIAN</h1>
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
        <th>No. Pembelian</th>
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
    <?php ($sub_diskon = 0); ?>
    <?php ($sub_ppn = 0); ?>
    <?php ($sub_biaya = 0); ?>
    <?php ($total_diskon = 0); ?>
    <?php ($total_ppn = 0); ?>
    <?php ($total_biaya = 0); ?>
    <?php $__currentLoopData = $pembelian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($temp != $value->pembelian->fid_supplier): ?>
            <?php if($total_before > 0): ?>
                <tr>
                    <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
                    <td style="text-align: right;"><b><?php echo e(format_number($total_before - $sub_diskon + $sub_ppn)); ?></b></td>
                    <td></td>
                </tr>
                <?php ($total_diskon += $sub_diskon); ?>
                <?php ($total_ppn += $sub_ppn); ?>
                <?php ($total_biaya += $sub_biaya); ?>
            <?php endif; ?>
            <tr>
                <td colspan="8"><b><?php echo e($value->pembelian->supplier->nama_supplier ?? ''); ?></b></td>
            </tr>
            <?php ($total_before = 0); ?>
        <?php endif; ?>
        <tr>
            <td><?php echo e($value->pembelian->no_pembelian ?? ''); ?></td>
            <td><?php echo e(format_date($value->pembelian->created_at ?? '')); ?></td>
            <td><?php echo e($value->produk->nama_produk ?? ''); ?> - <?php echo e($value->produk->kode ?? ''); ?></td>
            <td><?php echo e($value->produk->kategori_produk->nama_kategori ?? ''); ?></td>
            <td style="text-align:right"><?php echo e(format_number($value->harga)); ?></td>
            <td style="text-align:right"><?php echo e(format_number($value->jumlah)); ?></td>
            <td style="text-align:right"><?php echo e(format_number($value->total)); ?></td>
            <td><?php echo e($value->produk->satuan_barang->satuan ?? ''); ?></td>
        </tr>
        <?php ($temp = $value->pembelian->fid_supplier); ?>
        <?php ($total_before += $value->total); ?>
        <?php ($sub_diskon = $value->pembelian->diskon_nominal); ?>
        <?php ($sub_ppn = $value->pembelian->ppn_nominal); ?>
        <?php ($sub_biaya = $value->pembelian->biaya_tambahan); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
        <td style="text-align: right;"><b><?php echo e(format_number($total_before - $sub_diskon + $sub_ppn + $sub_biaya)); ?></b></td>
        <td></td>
    </tr>
    <?php ($total_diskon += $sub_diskon); ?>
    <?php ($total_ppn += $sub_ppn); ?>
    <?php ($total_biaya += $sub_biaya); ?>
    <tr>
        <td colspan="6"><b>TOTAL</b></td>
        <td style="text-align:right"><b><?php echo e(format_number($pembelian->sum('total') - $total_diskon + $total_ppn + $total_biaya)); ?></b></td>
        <td></td>
    </tr>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.report2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/laporan/pembelian/laporan.blade.php ENDPATH**/ ?>