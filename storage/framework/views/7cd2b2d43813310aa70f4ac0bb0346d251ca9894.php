<?php $__env->startSection('content'); ?>
    <h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
    <h1 style="text-align: center;margin: 0;">LAPORAN PENJUALAN</h1>
    <p style="margin: 0;text-align: center;">
        <?php if(($params['tanggal_awal'] ?? '') != ''): ?>
            <?php echo e(format_date($params['tanggal_awal'] ?? '')); ?>

        <?php endif; ?>
        <?php if(($params['tanggal_akhir'] ?? '') != ''): ?>
            s/d <?php echo e(format_date($params['tanggal_akhir'] ?? '')); ?>

        <?php endif; ?>
    </p>
    <br>
    <table class="table table-bordered table-sm">
        <thead>
        <tr>
            <th>Anggota</th>
            <th>No. Penjualan</th>
            <th>Tanggal</th>
            <th>Barang</th>
            <th>Kategori</th>
            <th style="text-align:right">Harga</th>
            <th style="text-align:right">Jumlah</th>
            <th style="text-align:right">Subtotal</th>
            <th>Satuan</th>
            <th>Laba</th>
        </tr>
        </thead>
        <tbody>
        <?php ($temp = ''); ?>
        <?php ($temp2 = ''); ?>
        <?php ($total_before = 0); ?>
        <?php ($total_before2 = 0); ?>
        <?php ($sub_diskon = 0); ?>
        <?php ($total_diskon = 0); ?>
        <?php ($total_diskon2 = 0); ?>
        <?php ($sub_margin = 0); ?>
        <?php ($total_margin = 0); ?>
        <?php $__currentLoopData = $penjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($key > 0): ?>
                <?php if($temp2 != $value->fid_penjualan): ?>
                    <tr>
                        <td colspan="7" style="text-align: right;">Diskon</td>
                        <td style="text-align: right;"><?php echo e(($sub_diskon)); ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align: right;">Total Transaksi</td>
                        <td style="text-align: right;"><?php echo e(($total_before2 - $sub_diskon)); ?></td>
                        <td></td>
                        <td style="text-align: right;"><?php echo e($sub_margin); ?></td>
                    </tr>
                    <?php ($total_diskon += $sub_diskon); ?>
                    <?php ($total_diskon2 += $sub_diskon); ?>
                    <?php ($total_margin += $sub_margin); ?>
                    <?php ($sub_diskon = 0); ?>
                    <?php ($sub_margin = 0); ?>
                    <?php ($total_before2 = 0); ?>
                <?php endif; ?>
                <?php if($temp != $value->penjualan->fid_anggota): ?>
                    <tr>
                        <td colspan="7" style="text-align: right;"><b>Sub Diskon</b></td>
                        <td style="text-align: right;"><b><?php echo e(($total_diskon2)); ?></b></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align: right;"><b>Sub Total</b></td>
                        <td style="text-align: right;"><b><?php echo e(($total_before - $total_diskon2)); ?></b></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="9"><b><?php echo e($value->penjualan->fid_anggota ?? ''); ?> - <?php echo e($value->penjualan->anggota->nama_lengkap ?? ''); ?></b></td>
                    </tr>
                    <?php ($total_before = 0); ?>
                    <?php ($total_diskon2 = 0); ?>
                <?php endif; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9"><b><?php echo e($value->penjualan->fid_anggota ?? ''); ?> - <?php echo e($value->penjualan->anggota->nama_lengkap ?? ''); ?></b></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td></td>
                <td><?php echo e($value->penjualan->no_transaksi ?? ''); ?></td>
                <td><?php echo e(format_date($value->penjualan->created_at ?? '')); ?></td>
                <td>
                    <?php if(!empty($value->produk)): ?>
                        <?php echo e($value->produk->nama_produk ?? ''); ?> - <?php echo e($value->produk->kode ?? ''); ?>

                    <?php else: ?>
                        <?php echo e($value->nama_barang); ?>

                    <?php endif; ?>
                </td>
                <td><?php echo e($value->produk->kategori_produk->nama_kategori ?? ''); ?></td>
                <td style="text-align:right"><?php echo e(($value->harga)); ?></td>
                <td style="text-align:right"><?php echo e(($value->jumlah)); ?></td>
                <td style="text-align:right"><?php echo e(($value->total)); ?></td>
                <td><?php echo e($value->produk->satuan_barang->satuan ?? ''); ?></td>
                <td style="text-align:right"><?php echo e(($value->margin_nominal)); ?></td>
            </tr>
            <?php ($temp = $value->penjualan->fid_anggota); ?>
            <?php ($temp2 = $value->fid_penjualan); ?>
            <?php ($total_before += $value->total); ?>
            <?php ($total_before2 += $value->total); ?>
            <?php ($sub_diskon = $value->penjualan->diskon); ?>
            <?php ($sub_margin += $value->margin_nominal); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td colspan="7" style="text-align: right;">Diskon</td>
            <td style="text-align: right;"><?php echo e(($sub_diskon)); ?></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right;">Total Transaksi</td>
            <td style="text-align: right;"><?php echo e(($total_before2 - $sub_diskon)); ?></td>
            <td></td>
            <td style="text-align: right;"><?php echo e($sub_margin); ?></td>
        </tr>
        <?php ($total_diskon += $sub_diskon); ?>
        <?php ($total_diskon2 += $sub_diskon); ?>
        <tr>
            <td colspan="7" style="text-align: right;"><b>Sub Diskon</b></td>
            <td style="text-align: right;"><b><?php echo e(($total_diskon2)); ?></b></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right;"><b>Sub Total</b></td>
            <td style="text-align: right;"><b><?php echo e(($total_before - $total_diskon2)); ?></b></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right;"><b>Total</b></td>
            <td style="text-align: right;"><b><?php echo e(($penjualan->sum('total') - $total_diskon)); ?></b></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right;"><b>Total Laba</b></td>
            <td style="text-align: right;"><b><?php echo e(($total_margin)); ?></b></td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.report2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/laporan/penjualan/laporan.blade.php ENDPATH**/ ?>