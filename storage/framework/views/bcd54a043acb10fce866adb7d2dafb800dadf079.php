<div class="row mt-3">
    <div class="col-md-6">
        <h2># Pembelian</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>No.Pembelian</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $pembelian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($value->pembelian->no_pembelian); ?></td>
                    <td style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->pembelian->tanggal,'d/m/Y')); ?></td>
                    <td class="center"><?php echo e($value->jumlah); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td colspan="2">Total</td>
                <td class="center"><?php echo e($pembelian->sum('jumlah')); ?></td>
            </tr>
            </tbody>
        </table>
        <h2 class="mt-3"># Retur Pembelian</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>No. Retur Pembelian</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $retur_pembelian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($value->retur_pembelian->no_retur); ?></td>
                    <td style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->retur_pembelian->tanggal,'d/m/Y')); ?></td>
                    <td class="center"><?php echo e($value->jumlah); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td colspan="2">Total</td>
                <td class="center"><?php echo e($retur_pembelian->sum('jumlah')); ?></td>
            </tr>
            </tbody>
        </table>
        <h2 class="mt-3"># Penyesuaian Stok</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>Keterangan</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $opname; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($value->keterangan); ?></td>
                    <td style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')); ?></td>
                    <td class="center"><?php echo e($value->jumlah); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td colspan="2">Total</td>
                <td class="center"><?php echo e($opname->sum('jumlah')); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-6">
        <h2># Penjualan</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>No.Penjualan</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $penjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($value->penjualan->no_transaksi); ?></td>
                    <td style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->penjualan->tanggal,'d/m/Y')); ?></td>
                    <td class="center"><?php echo e($value->jumlah); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td colspan="2">Total</td>
                <td class="center"><?php echo e($penjualan->sum('jumlah')); ?></td>
            </tr>
            </tbody>
        </table>
        <h2 class="mt-3"># Retur Penjualan</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>No. Retur Penjualan</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $retur_penjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($value->retur_penjualan->no_retur); ?></td>
                    <td style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->retur_penjualan->tanggal,'d/m/Y')); ?></td>
                    <td class="center"><?php echo e($value->jumlah); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td colspan="2">Total</td>
                <td class="center"><?php echo e($retur_penjualan->sum('jumlah')); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/pos/laporan/mutasi/_table.blade.php ENDPATH**/ ?>