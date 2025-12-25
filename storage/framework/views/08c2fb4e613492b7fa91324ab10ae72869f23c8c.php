<table class="table table-middle table-hover m-0 mt-3">
    <thead>
    <tr style="background:#eff2f7">
        <th width="20px">No</th>
        <th>Nama Barang</th>
        <th class="center" style="width:150px">Jumlah</th>
        <th style="text-align:right;width:120px">Harga</th>
        <th class="center" style="width:90px">Diskon</th>
        <th style="text-align:right;width:120px">Sub Total</th>
        <th style="width:50px"></th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($key+1); ?></td>
            <td>
                <div class="media">
                    <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px;border:1px solid #e4e4e4">
                        <img src="<?php echo e($item->produk->foto_url ?? ''); ?>" alt="" />
                    </div>
                    <div class="align-self-center media-body">
                        <span>Kode. <?php echo e($item->produk->kode ?? ''); ?></span>
                        <h6><?php echo e($item->produk->nama_produk ?? ''); ?></h6>
                    </div>
                </div>
            </td>
            <td class="align-middle" style="width: 80px;">
                <input id="jumlah_<?php echo e($item->id); ?>" value="<?php echo e($item->jumlah); ?>" class="form-control text-center autonumeric" type="text" onchange="update_item('<?php echo e($item->id); ?>')" />
            </td>
            <td class="align-middle text-right"><?php echo e(format_number($item->harga)); ?></td>
            <td class="align-middle">
                <input id="diskon_<?php echo e($item->id); ?>" value="<?php echo e(format_number($item->diskon)); ?>" class="form-control text-center autonumeric" type="text" onchange="update_item('<?php echo e($item->id); ?>')" />
            </td>
            <td class="align-middle text-right"><?php echo e(format_number($item->total)); ?></td>
            <td style="width:50px">
                <div class="text-center">
                    <a href="javascript:void(0)" onclick="delete_item(<?php echo e($item->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                </div>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<script>
    $total.html(add_commas('<?php echo e($items->sum('total')); ?>'));
    $total2.val(add_commas('<?php echo e($items->sum('total')); ?>'));
    total = parseFloat('<?php echo e($items->sum('total')); ?>');
    total_diskon = parseFloat('<?php echo e($items->sum('diskon')); ?>');
    $('#kembali').val(add_commas('<?php echo e(0 -$items->sum('total')); ?>'));

    <?php if(count($items) == 0): ?>
        $('#button_bayar').hide();
    <?php else: ?>
    $('#button_bayar').show();
    <?php endif; ?>
</script>
<?php /**PATH /var/www/html/resources/views/pos/penjualan_baru/_items.blade.php ENDPATH**/ ?>