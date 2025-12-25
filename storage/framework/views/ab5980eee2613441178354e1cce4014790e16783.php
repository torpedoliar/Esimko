<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>Barang</th>
            <th class="center">Harga Beli</th>
            <th class="center">Jumlah</th>
            <th class="center">Jenis</th>
            <th>Keterangan</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $stokOpname; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                <td class="center"><?php echo e($value->hpp); ?></td>
                <td class="center"><?php echo e($value->jumlah); ?></td>
                <td class="center"><?php echo e($value->jenis); ?></td>
                <td><?php echo e($value->keterangan); ?></td>
                <td style="width:1px;white-space:nowrap">
                    <a href="<?php echo e(url('manajemen_stok/stok_opname/'. $value->id .'/edit?page='.$stokOpname->currentPage())); ?>" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                    <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                    <form action="<?php echo e(url('manajemen_stok/stok_opname/' . $value->id. '?page=' . $stokOpname->currentPage())); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
                        <?php echo e(csrf_field()); ?>

                        <input type="hidden" name="id" value="<?php echo e($value->id); ?>">
                        <input type="hidden" name="_method" value="delete">
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php echo e($stokOpname->links('include.custom', ['function' => 'search_data'])); ?>

<?php /**PATH /var/www/html/resources/views/manajemen_stok/stok_opname/_table.blade.php ENDPATH**/ ?>