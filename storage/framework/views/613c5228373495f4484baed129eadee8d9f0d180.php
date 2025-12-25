<table>
    <thead>
    <tr>
        <th colspan="11">PERIODE : <?php echo e($bulan != 'all' ? list_bulan()[$bulan] : ''); ?> <?php echo e($tahun); ?></th>
    </tr>
    <tr>
        <th class="center">Tanggal</th>
        <th>No.Anggota</th>
        <th>Nama Lengkap</th>
        <th class="center" width="200px">Jenis Pinjaman</th>
        <th style="text-align:right">Jumlah Pinjaman</th>
        <th style="text-align:right">Total Angsuran</th>
        <th style="text-align:right">Sisa Pinjaman</th>
        <th style="text-align:right">Sisa Tenor</th>
        <th style="text-align:right">Angsuran Ke</th>
        <th style="text-align:right">Angsuran Total</th>
        <th style="text-align:right">Status</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td class="center" style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')); ?></td>
            <td><?php echo e($value->fid_anggota); ?></td>
            <td><?php echo e($value->nama_lengkap); ?></td>
            <td class="center"><?php echo e($value->jenis_transaksi); ?></td>
            <td style="text-align:right;white-space:nowrap">Rp <?php echo e(format_number(str_replace('-','',$value->nominal))); ?></td>
            <td style="text-align:right;white-space:nowrap">Rp <?php echo e(format_number(str_replace('-','',$value->total_angsuran))); ?></td>
            <td style="text-align:right;white-space:nowrap"><h6>Rp <?php echo e(format_number(str_replace('-','',$value->sisa_pinjaman))); ?></h6></td>
            <td style="text-align: right"><?php echo e($value->sisa_tenor); ?></td>
            <td style="text-align: right"><?php echo e($value->tenor-$value->sisa_tenor); ?></td>
            <td><?php echo e($value->tenor); ?></td>
            <td style="text-align: right"><?php echo e($value->status); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH /var/www/html/resources/views/pinjaman/pengajuan/export.blade.php ENDPATH**/ ?>