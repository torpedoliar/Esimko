<div class="row mt-4 mb-4">
  <div class="col-md-7">
    <div style="padding-top:20px;padding-bottom:20px;height:100%">
      <h5 class="mb-3">Informasi Barang</h5>
      <table class="table table-informasi">
        <tr>
          <th width="200px">Kode</th>
          <th width="10px">:</th>
          <td><?php echo e($data['produk']->kode); ?></td>
        </tr>
        <tr>
          <th>Nama Produk</th>
          <th>:</th>
          <td><?php echo e($data['produk']->nama_produk); ?></td>
        </tr>
        <tr>
          <th>Kelompok Barang</th>
          <th>:</th>
          <td><?php echo e($data['produk']->kelompok); ?></td>
        </tr>
        <tr>
          <th>Kategori</th>
          <th>:</th>
          <td><?php echo e($data['produk']->kategori); ?></td>
        </tr>
        <tr>
          <th>Sub Kategori</th>
          <th>:</th>
          <td><?php echo e($data['produk']->sub_kategori); ?></td>
        </tr>
        <tr>
          <th>Satuan</th>
          <th>:</th>
          <td><?php echo e($data['produk']->satuan); ?></td>
        </tr>
        <tr>
          <th>Expired</th>
          <th>:</th>
          <td><?php echo e(format_date($data['produk']->expired)); ?></td>
        </tr>
      </table>
      <h5 class="mb-3">Deskripsi Barang</h5>
      <p><?php echo e((!empty($data['produk']->deskripsi) ? $data['produk']->deskripsi : 'Tidak ada Deskripsi')); ?></p>
    </div>
  </div>
  <div class="col-md-5">
    <div style="padding:20px 30px; border-left:1px solid rgb(0 0 0 / 7%);height:100%">
      <h5 class="mb-3">Informasi Stok</h5>
      <table class="table table-informasi">
        <tr>
          <th width="150px">Stok Awal</th>
          <th width="10px">:</th>
          <td><?php echo e($data['produk']->stok_awal); ?> <?php echo e($data['produk']->satuan); ?></td>
        </tr>
        <tr>
          <th>Pembelian</th>
          <th>:</th>
          <td><?php echo e($data['produk']->pembelian); ?> <?php echo e($data['produk']->satuan); ?></td>
        </tr>
        <tr>
          <th>Retur Pembelian</th>
          <th>:</th>
          <td><?php echo e($data['produk']->retur); ?> <?php echo e($data['produk']->satuan); ?></td>
        </tr>
        <tr>
          <th>Penjualan</th>
          <th>:</th>
          <td><?php echo e($data['produk']->terjual); ?> <?php echo e($data['produk']->satuan); ?></td>
        </tr>
        <tr>
          <th>Sisa Stok</th>
          <th>:</th>
          <td><?php echo e($data['produk']->sisa); ?> <?php echo e($data['produk']->satuan); ?></td>
        </tr>
        <tr>
          <th>Stok Minimal</th>
          <th>:</th>
          <td><?php echo e($data['produk']->stok_minimal); ?> <?php echo e($data['produk']->satuan); ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>
<?php /**PATH /var/www/html/resources/views/manajemen_stok/barang/detail/informasi.blade.php ENDPATH**/ ?>