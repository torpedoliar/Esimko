<?php
  $subpage='Belanja '.ucfirst($jenis);
  $keterangan='Halaman riwayat belanja '.$jenis.' anggota';
?>

<?php $__env->startSection('content_belanja'); ?>
<div class="card">
  <div class="card-body">
    <div class="center mb-5">
      <img src="<?php echo e(asset('assets/images/'.$data['penjualan']->icon)); ?>" style="width:80px">
      <h4 class="mt-3"><?php echo e($data['keterangan']->label); ?></h4>
      <p><?php echo e($data['keterangan']->keterangan); ?></p>
    </div>
  </div>
  <div class="card-header" style="background:#eaecef">
    <ul class="nav nav-pills" role="tablist">
      <li class="nav-item waves-effect waves-light">
        <a class="nav-link active" data-toggle="tab" href="#informasi" role="tab">
          <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
          <span class="d-none d-sm-block">Informasi Belanja</span>
        </a>
      </li>
      <li class="nav-item waves-effect waves-light">
        <a class="nav-link" data-toggle="tab" href="#items" role="tab">
          <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
          <span class="d-none d-sm-block">Rincian Belanja</span>
        </a>
      </li>
      <?php if($data['penjualan']->fid_metode_pembayaran==3): ?>
      <li class="nav-item waves-effect waves-light">
        <a class="nav-link" data-toggle="tab" href="#angsuran" role="tab">
          <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
          <span class="d-none d-sm-block">Angsuran Belanja</span>
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content">
      <div class="tab-pane active" id="informasi" role="tabpanel">
        <h5 class="mb-3">Informasi Belanja</h5>
        <table class="table table-informasi">
          <tr>
            <th width="180px">Tanggal, Waktu</th>
            <th width="10px">:</th>
            <td><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($data['penjualan']->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($data['penjualan']->created_at,'H:i:s')); ?></td>
          </tr>
          <tr>
            <th>No. Transaksi</th>
            <th>:</th>
            <td><?php echo e(ucfirst($data['penjualan']->no_transaksi)); ?></td>
          </tr>
          <tr>
            <th>No. Anggota</th>
            <th>:</th>
            <td><?php echo e($data['penjualan']->no_anggota); ?></td>
          </tr>
          <tr>
            <th>Nama Lengkap</th>
            <th>:</th>
            <td><?php echo e($data['penjualan']->nama_lengkap); ?></td>
          </tr>
          <tr>
            <th>Jenis Belanja</th>
            <th>:</th>
            <td>Belanja <?php echo e(ucfirst($data['penjualan']->jenis_belanja)); ?></td>
          </tr>
          <tr>
            <th>Metode Pembayaran</th>
            <th>:</th>
            <td><?php echo e($data['penjualan']->metode_pembayaran); ?></td>
          </tr>
          <tr>
            <th>Total Belanja</th>
            <th>:</th>
            <td>Rp <?php echo e(number_format(str_replace('-','',$data['penjualan']->total_pembayaran),0,',','.')); ?></td>
          </tr>
          <?php if($data['penjualan']->voucher_nominal!=0): ?>
          <tr>
            <th>Kode Voucher</th>
            <th>:</th>
            <td><?php echo e($data['penjualan']->kode_voucher); ?></td>
          </tr>
          <tr>
            <th>Voucher Belanja</th>
            <th>:</th>
            <td>Rp <?php echo e(number_format($data['penjualan']->voucher_nominal,0,',','.')); ?></td>
          </tr>
          <?php endif; ?>
          <tr class="data_1 data_hide">
            <th>Tunai</th>
            <th>:</th>
            <td>Rp <?php echo e(number_format($data['penjualan']->tunai,0,',','.')); ?></td>
          </tr>
          <tr class="data_1 data_hide">
            <th>Kembali</th>
            <th>:</th>
            <td>Rp <?php echo e(number_format($data['penjualan']->kembali,0,',','.')); ?></td>
          </tr>
          <tr class="data_3 data_hide">
            <th>Tenor</th>
            <th>:</th>
            <td><?php echo e($data['penjualan']->tenor); ?> Bulan</td>
          </tr>
          <tr class="data_3 data_hide">
            <th>Angsuran</th>
            <th>:</th>
            <td>Rp <?php echo e(number_format($data['penjualan']->angsuran,0,',','.')); ?></td>
          </tr>
          <tr class="data_3 data_hide">
            <th>Status Angsuran</th>
            <th>:</th>
            <td>Belum Lunas</td>
          </tr>
          <tr class="data_5 data_hide">
            <th>Nomor Debet Card </th>
            <th>:</th>
            <td><?php echo e($data['penjualan']->no_debit_card); ?></td>
          </tr>
          <tr class="data_7 data_hide">
            <th>Nomor Akun <?php echo e($data['penjualan']->metode_pembayaran); ?></th>
            <th>:</th>
            <td><?php echo e($data['penjualan']->account_number); ?></td>
          </tr>
          <tr>
            <th>Keterangan</th>
            <th>:</th>
            <td><?php echo e((!empty($data['penjualan']->keterangan) ? $data['penjualan']->keterangan : 'Tidak ada keterangan')); ?></td>
          </tr>
        </table>
        <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
        <ul class="verti-timeline list-unstyled">
          <li class="event-list">
            <div class="event-timeline-dot">
              <i class="bx bx-right-arrow-circle"></i>
            </div>
            <h6><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($data['penjualan']->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($data['penjualan']->created_at,'H:i:s')); ?></h6>
            <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500"><?php echo e($data['penjualan']->nama_petugas); ?></span></p>
          </li>
          <?php $__currentLoopData = \App\Helpers\GlobalHelper::get_verifikasi_transaksi($id,($jenis=='toko' ? 'penjualan' : 'kredit belanja')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li class="event-list">
            <div class="event-timeline-dot">
              <i class="bx bx-right-arrow-circle"></i>
            </div>
            <h6><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')); ?></h6>
            <p class="text-muted"><?php echo e($value->caption); ?> <span style="font-weight:500"><?php echo e($value->nama_lengkap); ?></span></p>
          </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div>
      <div class="tab-pane" id="items" role="tabpanel">
        <h5 class="mb-3">Rincian Belanja</h5>
        <table class="table table-middle table-hover mt-3">
          <thead>
            <tr>
              <th>Nama Barang</th>
              <th class="center">Jumlah</th>
              <th style="text-align:right">Harga Satuan</th>
              <th style="text-align:right;width:135px">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $data['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <td>
                <div class="media">
                  <div class="rounded mr-3 produk-wrapper m-0" style="height:40px;width:40px">
                    <img src="<?php echo e((!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                  </div>
                  <div class="align-self-center media-body">
                    <?php if($jenis=='toko'): ?><span>Kode. <?php echo e($value->kode); ?></span><?php endif; ?>
                    <h6><?php echo e(($jenis=='toko' ? $value->nama_produk : $value->nama_barang)); ?></h6>
                  </div>
                </div>
              </td>
              <td class="center"><?php echo e($value->jumlah); ?> <?php echo e($value->satuan); ?></td>
              <td style="text-align:right;width:135px" >Rp <?php echo e(number_format($value->harga,0,',','.')); ?></td>
              <td style="text-align:right;width:135px" >Rp <?php echo e(number_format($value->total,0,',','.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
      <?php if($data['penjualan']->fid_metode_pembayaran==3): ?>
      <div class="tab-pane" id="angsuran" role="tabpanel">
        <h5 class="mb-3">Angsuran Kredit Belanja</h5>
        <table class="table table-middle table-bordered table-hover mt-3">
          <thead class="thead-light">
            <tr>
              <th style="width:1px;white-space:nowrap">Ke</th>
              <th style="text-align:right">Angsuran</th>
              <th class="center">Payroll Bulan</th>
              <th style="text-align:right">Sisa<br>Angsuran</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $data['angsuran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td><?php echo e($value->angsuran_ke); ?></td>
                <td style="text-align:right"><?php echo e(number_format($value->total_angsuran,'0',',','.')); ?></td>
                <td class="center" ><?php echo e(($value->fid_payroll == null ? 'Belum Dipayroll' : \App\Helpers\GlobalHelper::nama_bulan($value->bulan) )); ?>  </td>
                <td style="text-align:right"><?php echo e(number_format($value->sisa_angsuran,'0',',','.')); ?></td>
                <td class="center" style="width:1px;white-space:nowrap">
                  <span style="background:<?php echo e($value->color); ?>;padding:3px 6px;color:#fff;font-size:11px"><?php echo e($value->status_angsuran); ?></span>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="card-footer">
    <div class="pull-right">
      <?php if($data['penjualan']->fid_status==1): ?>
      <a href="<?php echo e(url('main/belanja/riwayat/toko')); ?>" class="btn btn-secondary">Kembali</a>
      <button class="btn btn-danger" type="button" onclick="batalkan()">Batalkan Transaksi</button>
      <?php else: ?>
      <a href="<?php echo e(url('main/belanja/riwayat/toko')); ?>" class="btn btn-secondary">Kembali</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<form action="<?php echo e(url('main/belanja/riwayat/'.$jenis.'/proses_pembatalan')); ?>" method="post" id="proses_pembatalan">
  <?php echo e(csrf_field()); ?>

  <input type="hidden" name="id" value="<?php echo e($id); ?>">
</form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('add_js'); ?>
<script type="text/javascript" src="<?php echo e(asset('assets/js/jquery.qrcode.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(asset('assets/js/qrcode.js')); ?>"></script>
<script>

function batalkan(){
  Swal.fire({
    title: "Are you sure?",
    text: 'Apakah anda yakin ingin mebatalkan transaksi ini',
    type:"question",
    showCancelButton: true,
    confirmButtonColor: '#d63030',
    cancelButtonColor: '#cbcbcb',
    confirmButtonText: 'Yes'
  }).then((result) => {
    if (result.value == true) {
      $('#proses_pembatalan').submit();
    }
  });
}
$('.data_hide').hide();
$('.data_<?php echo e($data['penjualan']->fid_metode_pembayaran); ?>').show();
// $('#qrcode').qrcode({
//   width: 100,
//   height: 100,
// 	text	: "<?php echo e($data['penjualan']->no_transaksi); ?>"
// });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main.belanja.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/main/belanja/riwayat/detail.blade.php ENDPATH**/ ?>