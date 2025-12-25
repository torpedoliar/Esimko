<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>Kode / Nama Produk</th>
            <th class="center">Kategori Produk</th>
            <th style="text-align:right">Harga Beli</th>
            <th style="text-align:right">Margin</th>
            <th style="text-align:right">Harga Jual</th>
            <th style="text-align:right">Stok Sisa</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $produk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($key == 0): ?>
                <script>
                    produk_pertama = '<?php echo e($value->kode); ?>';
                </script>
            <?php endif; ?>
            <tr onclick="pilih_produk('<?php echo e($value->kode); ?>')">
                <td>
                    <div class="media">
                        <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                            <img src="<?php echo e($value->foto_url); ?>" alt="" />
                        </div>
                        <div class="align-self-center media-body">
                            <span>Kode. <?php echo e($value->kode); ?></span>
                            <h6><?php echo e($value->nama_produk); ?></h6>
                        </div>
                    </div>
                </td>
                <td class="center"><?php echo e($value->satuan_barang->satuan ?? ''); ?></td>
                <td style="text-align:right;white-space:nowrap">Rp <?php echo e(number_format($value->harga_beli,0,',','.')); ?></td>
                <td style="text-align:right;white-space:nowrap">(<?php echo e($value->margin); ?>%)<br>Rp <?php echo e(number_format($value->margin_nominal,0,',','.')); ?></td>
                <td style="text-align:right;white-space:nowrap">Rp <?php echo e(number_format($value->harga_jual,0,',','.')); ?></td>
                <td style="text-align:right;white-space:nowrap"><?php echo e($value->stok['sisa'] ?? ''); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php /**PATH /var/www/html/resources/views/pos/penjualan_baru/_list_produk.blade.php ENDPATH**/ ?>