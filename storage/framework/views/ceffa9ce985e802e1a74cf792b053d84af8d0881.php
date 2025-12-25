<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>Kode / Nama Produk</th>
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
                <td>
                    <div class="media">
                        <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                            <img src="<?php echo e((!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                        </div>
                        <div class="align-self-center media-body">
                            <span>Kode. <?php echo e($value->kode); ?></span>
                            <h6><?php echo e($value->nama_produk); ?></h6>
                        </div>
                    </div>
                </td>
                <td class="center">
                    <div style="font-weight:600"><?php echo e($value->kelompok); ?></div>
                    <div><?php echo e($value->kategori_produk->nama_kategori); ?></div>
                    <div class="text-muted"><?php echo e($value->sub_kategori); ?></div>
                </td>
                <td class="center"><?php echo e($value->stok['stok_awal']); ?><br><?php echo e($value->satuan_barang->satuan); ?></td>
                <td class="center"><?php echo e($value->stok['pembelian'] - $value->stok['retur']); ?><br><?php echo e($value->satuan_barang->satuan); ?></td>
                <td class="center"><?php echo e($value->stok['terjual']); ?><br><?php echo e($value->satuan_barang->satuan); ?></td>
                <td class="center"><?php echo e($value->stok['penyesuaian']); ?><br><?php echo e($value->satuan_barang->satuan); ?></td>
                <td class="center"><?php echo e($value->stok['sisa']); ?><br><?php echo e($value->satuan_barang->satuan); ?></td>
                <td style="text-align:right;white-space:nowrap">Rp <?php echo e(number_format($value->harga_beli,0,',','.')); ?></td>
                <td style="text-align:right;white-space:nowrap">(<?php echo e($value->margin); ?>%)<br>Rp <?php echo e(number_format($value->margin_nominal,0,',','.')); ?></td>
                <td style="text-align:right;white-space:nowrap">Rp <?php echo e(number_format($value->harga_jual,0,',','.')); ?></td>
            </tr>
            <?php ($total_stok_awal += $value->stok['stok_awal']); ?>
            <?php ($total_stok_masuk += ($value->stok['pembelian'] - $value->stok['retur'])); ?>
            <?php ($total_stok_keluar += ($value->stok['terjual'])); ?>
            <?php ($total_stok_peny += $value->stok['penyesuaian']); ?>
            <?php ($total_stok_sisa += $value->stok['sisa']); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <th colspan="2"><b>TOTAL</b></th>
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
</div>
<?php echo e($produk->links('include.custom', ['function' => 'search_data'])); ?>

<?php /**PATH /var/www/html/resources/views/pos/laporan/produk/_table.blade.php ENDPATH**/ ?>