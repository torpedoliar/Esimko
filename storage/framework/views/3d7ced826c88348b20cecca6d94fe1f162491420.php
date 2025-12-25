<?php
  $page='Dashboard';
  $subpage='Dashboard';
?>
<?php $__env->startSection('title'); ?>
Dashboard |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <style>
  .nav-pills>li>a, .nav-tabs>li>a {
    color: #2f2f2f;
    font-weight: 400;
  }
  .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
    color: #fff;
    background-color: #1a4f73;
  }
  .nav-pills .nav-link {
    border-radius: 0px;
  }
  .card-title {
    font-size: 15px;
    margin: 0px;
    font-weight: 500;
    letter-spacing: 0.5px
  }
  .verti-timeline .event-list {
    position: relative;
    padding: 0 0 0px 20px;
  }
  .table-hover tr td{
    cursor: pointer;
  }
  .list-berita{
    padding:20px;
    border-bottom: 1px solid #e6e6e6;
    display: block
  }
  .list-berita:hover h5{
    color:#429d9c
  }
  .list-berita .produk-wrapper{
    margin:0px
  }
  </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-3">
            <div class="media">
              <img src="<?php echo e(asset('assets/images/icon-page/wallet.png')); ?>" style="height:70px;margin-right:10px">
              <div class="media-body align-self-center">
                <p class="text-muted mb-1">Saldo Simpanan</p>
                <h5 class="font-size-17">Rp <?php echo e(number_format($data['saldo-simpanan'],0,',','.')); ?></h5>
              </div>
            </div>
          </div>
          <div class="col-3">
            <div class="media">
              <img src="<?php echo e(asset('assets/images/icon-page/save-money.png')); ?>" style="height:70px;margin-right:10px">
              <div class="media-body align-self-center">
                <p class="text-muted mb-1">Sisa Pinjaman</p>
                <h5 class="font-size-17">Rp <?php echo e(number_format(str_replace('-','',$data['sisa-pinjaman']),0,',','.')); ?></h5>
              </div>
            </div>
          </div>
          <div class="col-3">
            <div class="media">
              <img src="<?php echo e(asset('assets/images/icon-page/pay-day.png')); ?>" style="height:70px;margin-right:10px">
              <div class="media-body align-self-center">
                <p class="text-muted mb-1">Total Angsuran</p>
                <h5 class="font-size-17">Rp <?php echo e(number_format($data['total-angsuran'],0,',','.')); ?></h5>
              </div>
            </div>
          </div>
          <div class="col-3">
            <div class="media">
              <img src="<?php echo e(asset('assets/images/icon-page/shopping-basket.png')); ?>" style="height:70px;margin-right:10px">
              <div class="media-body align-self-center">
                <p class="text-muted mb-1">Total Kredit Belanja</p>
                <h5 class="font-size-17">Rp <?php echo e(number_format($data['total-angsuran-belanja'],0,',','.')); ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <?php
        $jenis=array('simpanan'=>'Simpanan','pinjaman'=>'Pinjaman');
      ?>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body p-0">
            <div class="p-3">
              <h4 class="card-title">Transaksi Terakhir</h4>
            </div>
            <ul class="nav nav-pills" style="background:#f2f2f5" role="tablist">
              <?php $__currentLoopData = $jenis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li class="nav-item">
                <a class="nav-link <?php echo e(($key=='simpanan' ? 'active' : '')); ?>" data-toggle="tab" href="#<?php echo e($key); ?>" role="tab"><?php echo e($value); ?></a>
              </li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <div class="table-responsive" data-simplebar style="height:300px;">
              <div class="tab-content">
                <?php $__currentLoopData = $jenis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <div class="tab-pane <?php echo e(($key=='simpanan' ? 'active' : '')); ?>" id="<?php echo e($key); ?>" role="tabpanel">
                    <?php if(count($data['transaksi-terakhir'][$key])==0): ?>
                    <div style="width:100%;text-align:center">
                      <img src="<?php echo e(asset('assets/images/icon-page/proses.png')); ?>" class="mt-5" style="width:80px">
                      <p class="font-size-14 mt-3">Tidak ada Transkasi</p>
                    </div>
                    <?php else: ?>
                    <table class="table table-middle table-hover">
                      <tbody>
                        <?php $__currentLoopData = $data['transaksi-terakhir'][$key]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key2 => $value2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <tr onclick="location.href = '<?php echo e(url('main/'.$key.'/detail?id='.$value2->id)); ?>'">
                            <td>
                              <h6><?php echo e($value2->jenis_transaksi); ?></h6>
                              <p class="text-muted mb-0"><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value2->tanggal)); ?></p>
                            </td>
                            <td style="text-align:right;">
                              <h6 class="text-truncate">Rp <?php echo e(number_format($value2->nominal,0,',','.')); ?></h6>
                              <span style="color:<?php echo e($value2->color); ?>"><?php echo e($value2->status); ?></span>
                            </td>
                          </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </tbody>
                    </table>
                    <?php endif; ?>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body p-0">
            <div class="p-3">
              <h4 class="card-title">Berita dan Informasi</h4>
            </div>
            <div data-simplebar style="height:335px;">
              <?php $__currentLoopData = $data['berita']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <a class="list-berita" href="<?php echo e(url('main/berita/detail?id='.$value->id)); ?>">
                <div class="media">
                  <div class="rounded produk-wrapper mr-3" style="height:100px;width:100px">
                    <img src="<?php echo e((!empty($value->gambar) ? asset('storage/'.$value->gambar) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                  </div>
                  <div class="media-body align-self-center">
                    <h5 class="mb-2 font-size-16"><?php echo e($value->judul); ?></h5>
                    <p class="text-muted"><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,"H:i:s")); ?></p>
                  </div>
                </div>
              </a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/main/dashboard.blade.php ENDPATH**/ ?>