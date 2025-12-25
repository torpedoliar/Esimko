<?php $__env->startSection('title'); ?>
    Cetak Struk Penjualan
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <style>
        .container-struk{
            font-size:10pt
        }
        .container-struk .header{
            text-align:center;
            font-size:14pt;
            border-bottom:1px solid #222222;
            padding-bottom:20px
        }

        .container-struk .informasi{
            border-bottom:1px solid #222222;
            padding-bottom:10px;
            padding-top:10px
        }

        .container-struk .items{
            border-bottom:1px solid #222222;
            padding-bottom:10px;
            padding-top:10px
        }

        .container-struk .items table tr th{
            text-align: left;
        }

        .container-struk .accounting{
            border-bottom:1px solid #222222;
            padding-top:20px
        }

        .container-struk .footer{
            text-align:center;
            font-size:12pt;
            font-weight:600;
            padding-top: 30px
        }


    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-struk">
        <div class="header">
            <div>CO-OP MART</div>
            <div>Kopkar Satya Sejahtera</div>
            <div>Ruko Citra Harmoni</div>
        </div>
        <div class="informasi">
            <table style="width:100%">
                <tr>
                    <td width="90px">Nota</td>
                    <td>:</td>
                    <td><?php echo e($penjualan->no_transaksi); ?></td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($penjualan->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($penjualan->created_at,'H:i:s')); ?></td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td><?php if(!empty($penjualan->anggota)): ?>(<?php echo e($penjualan->anggota->no_anggota); ?>) <?php echo e($penjualan->anggota->nama_lengkap); ?> <?php else: ?> Non Member <?php endif; ?></td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>:</td>
                    <td><?php echo e($penjualan->user_kasir->nama_lengkap ?? ''); ?></td>
                </tr>
            </table>
        </div>
        <div class="items">
            <table style="width:100%">
                <?php $__currentLoopData = $penjualan->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <th colspan="3"><?php echo e($value->produk->nama_produk); ?></th>
                        <th style="text-align:right"><?php echo e($value->produk->satuan); ?></th>
                    </tr>
                    <tr>
                        <td><?php echo e($value->jumlah); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($value->harga,2,'.',',')); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($value->nominal_diskon,2,'.',',')); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($value->total,2,'.',',')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total Tanpa Diskon</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->total_tanpa_diskon,2,'.',',')); ?></td>
                </tr>
                <tr>
                    <td>Total Diskon Barang</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->total_diskon,2,'.',',')); ?></td>
                </tr>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->subtotal,2,'.',',')); ?></td>
                </tr>
                <tr>
                    <td>Diskon Transaksi</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->diskon,2,'.',',')); ?></td>
                </tr>
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->total_pembayaran,2,'.',',')); ?></td>
                </tr>
                <?php if($penjualan->fid_metode_pembayaran==1): ?>
                    <tr>
                        <td>Tunai</td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->tunai,2,'.',',')); ?></td>
                    </tr>
                    <tr>
                        <td>Kembali</td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->tunai - $penjualan->total_pembayaran,2,'.',',')); ?></td>
                    </tr>
                <?php elseif($penjualan->fid_metode_pembayaran==3): ?>
                    <tr>
                        <td>Kredit</td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->total_pembayaran,2,'.',',')); ?></td>
                    </tr>
                <?php elseif($penjualan->fid_metode_pembayaran==5): ?>
                    <tr>
                        <td><?php echo e($penjualan->metode_pembayaran); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->total_pembayaran,2,'.',',')); ?></td>
                    </tr>
                    <tr>
                        <td>Nomor Debit Card</td>
                        <td style="text-align:right"><?php echo e($penjualan->no_debit_card); ?></td>
                    </tr>
                <?php elseif($penjualan->fid_metode_pembayaran==7): ?>
                    <tr>
                        <td><?php echo e($penjualan->metode_pembayaran); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->total_pembayaran,2,'.',',')); ?></td>
                    </tr>
                    <tr>
                        <td>Nomor Akun</td>
                        <td style="text-align:right"><?php echo e($penjualan->account_number); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="footer">
            <div>Terimakasih</div>
            <div>Silahkan datang kembali</div>
            <div>Cek Kembali Belanjaan anda</div>
            <div>Komplain tidak dilayani setelah meninggalkan toko</div>
        </div>
    </div>

    <div class="container-struk" style="page-break-before: always;">
        <div class="header">
            <div>CO-OP MART</div>
            <div>Kopkar Satya Sejahtera</div>
            <div>Ruko Citra Harmoni</div>
        </div>
        <div class="informasi">
            <table style="width:100%">
                <tr>
                    <td width="90px">Nota</td>
                    <td>:</td>
                    <td><?php echo e($penjualan->no_transaksi); ?></td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($penjualan->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($penjualan->created_at,'H:i:s')); ?></td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td><?php if(!empty($penjualan->anggota)): ?>(<?php echo e($penjualan->anggota->no_anggota); ?>) <?php echo e($penjualan->anggota->nama_lengkap); ?> <?php else: ?> Non Member <?php endif; ?></td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>:</td>
                    <td><?php echo e($penjualan->user_kasir->nama_lengkap ?? ''); ?></td>
                </tr>
            </table>
        </div>
        <div class="items">
            <table style="width:100%">
                <?php $__currentLoopData = $penjualan->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <th colspan="3"><?php echo e($value->produk->nama_produk); ?></th>
                        <th style="text-align:right"><?php echo e($value->produk->satuan); ?></th>
                    </tr>
                    <tr>
                        <td><?php echo e($value->jumlah); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($value->harga,2,'.',',')); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($value->nominal_diskon,2,'.',',')); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($value->total,2,'.',',')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total Tanpa Diskon</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->total_tanpa_diskon,2,'.',',')); ?></td>
                </tr>
                <tr>
                    <td>Total Diskon Barang</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->total_diskon,2,'.',',')); ?></td>
                </tr>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->subtotal,2,'.',',')); ?></td>
                </tr>
                <tr>
                    <td>Diskon Transaksi</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->diskon,2,'.',',')); ?></td>
                </tr>
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total</td>
                    <td style="text-align:right"><?php echo e(number_format($penjualan->total_pembayaran,2,'.',',')); ?></td>
                </tr>
                <?php if($penjualan->fid_metode_pembayaran==1): ?>
                    <tr>
                        <td>Tunai</td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->tunai,2,'.',',')); ?></td>
                    </tr>
                    <tr>
                        <td>Kembali</td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->kembali,2,'.',',')); ?></td>
                    </tr>
                <?php elseif($penjualan->fid_metode_pembayaran==3): ?>
                    <tr>
                        <td>Kredit</td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->total_pembayaran,2,'.',',')); ?></td>
                    </tr>
                <?php elseif($penjualan->fid_metode_pembayaran==5): ?>
                    <tr>
                        <td><?php echo e($penjualan->metode_pembayaran); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->total_pembayaran,2,'.',',')); ?></td>
                    </tr>
                    <tr>
                        <td>Nomor Debit Card</td>
                        <td style="text-align:right"><?php echo e($penjualan->no_debit_card); ?></td>
                    </tr>
                <?php elseif($penjualan->fid_metode_pembayaran==7): ?>
                    <tr>
                        <td><?php echo e($penjualan->metode_pembayaran); ?></td>
                        <td style="text-align:right"><?php echo e(number_format($penjualan->total_pembayaran,2,'.',',')); ?></td>
                    </tr>
                    <tr>
                        <td>Nomor Akun</td>
                        <td style="text-align:right"><?php echo e($penjualan->account_number); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="footer">
            <div>Terimakasih</div>
            <div>Silahkan datang kembali</div>
            <div>Cek Kembali Belanjaan anda</div>
            <div>Komplain tidak dilayani setelah meninggalkan toko</div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        window.print();
        $(window).keydown(function(event){
            console.log(event.keyCode);
            if (event.keyCode == 13) window.location.href = "<?php echo e(url('pos/penjualan_baru')); ?>";
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.report', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/penjualan_baru/cetak_struk.blade.php ENDPATH**/ ?>