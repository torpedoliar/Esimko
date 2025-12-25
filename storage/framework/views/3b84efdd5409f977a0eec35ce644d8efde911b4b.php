<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>Barang</th>
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
                <td>
                    <div class="media">
                        <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                            <img src="<?php echo e((!empty($value->produk->foto) ? asset('storage/'.$value->produk->foto) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                        </div>
                        <div class="align-self-center media-body">
                            <span>Kode. <?php echo e($value->produk->kode ?? ''); ?></span>
                            <h6><?php echo e($value->produk->nama_produk ?? ''); ?></h6>
                        </div>
                    </div>
                </td>
                <td class="center"><?php echo e($value->jumlah); ?></td>
                <td class="center"><?php echo e(format_number($value->jumlah * $value->hpp)); ?></td>
                <td class="center"><?php echo e($value->jenis); ?></td>
                <td><?php echo e($value->keterangan); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php echo e($penyesuaian->links('include.custom', ['function' => 'search_data'])); ?>

<?php /**PATH /var/www/html/resources/views/pos/laporan/penyesuaian/_table.blade.php ENDPATH**/ ?>