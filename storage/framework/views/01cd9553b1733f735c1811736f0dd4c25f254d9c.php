<?php
  $page='Belanja';
?>

<?php $__env->startSection('title'); ?>
<?php echo e($subpage); ?> |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <link href="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')); ?>" rel="stylesheet" />
  <style>
  .produk .card-body{
    padding: 0.7rem !important
  }

  .produk h6.title{
    font-weight: 500;
    line-height: 18px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
    white-space: normal;
    -webkit-line-clamp: 2;
    height:40px;
  }
  .produk .price{
    color:#2ecc71;
    font-weight:600;
    /* text-align:right */
  }
  .produk .discount{
    border-radius: 3px;
    background-color: var(--R100,#FFEAEF);
    font-size: 0.714286rem;
    font-weight: bold;
    padding: 2px 4px;
    color: var(--R400,#FF5C84);
  }

  .menu{
    list-style-type: none;
    margin: 0;
    padding: 0;
    width: 100%;
  }
  .menu li a i{
    font-size:23px;
    margin-right:10px;
    font-weight: 400;
    align-items:center
  }
  .menu li a {
    display: flex;
    color: #000;
    padding: 8px 16px;
    text-decoration: none;
    align-items:center
  }
  .menu li:last-child a{
    border-bottom:none;
  }

  .menu li a:hover {
    background-color:#f2f2f5;
  }

  .menu li a.active {
    color: #429d9c;
    font-weight: 500;
  }

  .menu li a:hover:not(.active) {
    background-color: #f2f2f5;
  }

  .cart-footer::before{
    content: '';
    position: absolute;
    top: -1.25rem;
    left: 0;
    height: 1.25rem;
    width: 100%;
    background: linear-gradient(transparent, rgba(0,0,0,0.06));
  }
  <?php echo $__env->yieldContent('add_css'); ?>
  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="<?php echo e(asset('assets/images/icon-page/shopping-basket.png')); ?>" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18"><?php echo e($subpage); ?></h4>
        <p class="text-muted m-0"><?php echo e($keterangan); ?></p>
      </div>
    </div>
  </div>
  <div class="row mb-5">
    <div class="col-auto">
      <div style="position:sticky;top:180px;width:200px;z-index:0">
        <ul class="menu mt-3">
          <li>
            <a href="<?php echo e(url('main/belanja')); ?>" class="<?php echo e(($subpage == 'Pilih Produk' ? 'active' : '')); ?>">
              <i class="bx bxs-widget" style="color:#16a085"></i>
              <div>Pilih Produk</div>
            </a>
          </li>
          <li>
            <a href="<?php echo e(url('main/belanja/riwayat/toko')); ?>" class="<?php echo e(($subpage == 'Belanja Toko' ? 'active' : '')); ?>">
              <i class="bx bxs-store" style="color:#f39c12"></i>
              <div>Belanja Toko</div>
            </a>
          </li>
          <li>
            <a href="<?php echo e(url('main/belanja/riwayat/konsinyasi')); ?>" class="<?php echo e(($subpage == 'Belanja Konsinyasi' ? 'active' : '')); ?>">
              <i class="bx bxs-briefcase-alt-2" style="color:#2980b9"></i>
              <div>Belanja Konsinyasi</div>
            </a>
          </li>
          <li>
            <a href="<?php echo e(url('main/belanja/riwayat/online')); ?>" class="<?php echo e(($subpage == 'Belanja Online' ? 'active' : '')); ?>">
              <i class="bx bx-wifi" style="color:#22a6b3"></i>
              <div>Belanja Online</div>
            </a>
          </li>
          <li>
            <a href="<?php echo e(url('main/belanja/retur')); ?>" class="<?php echo e(($subpage == 'Retur Barang' ? 'active' : '')); ?>">
              <i class="bx bx-transfer-alt" style="color:#4834d4"></i>
              <div>Retur Barang</div>
            </a>
          </li>
          <li>
            <a href="<?php echo e(url('main/belanja/angsuran')); ?>" class="<?php echo e(($subpage == 'Angsuran Belanja' ? 'active' : '')); ?>">
              <i class="bx bx-calendar" style="color:#6ab04c"></i>
              <div>Angsuran Belanja</div>
            </a>
          </li>
          <li>
            <a href="<?php echo e(url('main/belanja/keranjang')); ?>" class="<?php echo e(($subpage == 'Keranjang' ? 'active' : '')); ?>">
              <i class="bx bxs-cart-alt" style="color:#c0392b"></i>
              <div>Keranjang</div>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div class="col">
      <?php echo $__env->yieldContent('content_belanja'); ?>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script src="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')); ?>"></script>
  <script src="<?php echo e(asset('assets/js/pages/form-advanced.init.js')); ?>"></script>
  <script src="<?php echo e(asset('assets/js/accounting.js')); ?>"></script>
  <?php echo $__env->yieldContent('add_js'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/main/belanja/layout.blade.php ENDPATH**/ ?>