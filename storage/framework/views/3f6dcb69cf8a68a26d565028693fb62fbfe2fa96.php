<?php
  $page='Dashboard';
  $subpage=$tab;
?>

<?php $__env->startSection('title'); ?>
Profil |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <style>
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
  .table-informasi  tr td,
  .table-informasi  tr th{
    vertical-align: middle;
  }
  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="<?php echo e(asset('assets/images/icon-page/profile.png')); ?>" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Profil Anggota</h4>
        <p class="text-muted m-0">Menampilkan profil dari anggota koperasi</p>
      </div>
    </div>
  </div>
  <div class="row mb-2 mt-4">
    <div class="col-auto">
      <div style="position:sticky;top:180px;width:250px;z-index:0">
        <ul class="menu">
          <li>
            <a href="<?php echo e(url('main/profil?tab=informasi')); ?>" class="<?php echo e(($subpage == 'informasi' ? 'active' : '')); ?>">
              <i class="bx bxs-user" style="color:#16a085"></i>
              <div>Informasi Personal</div>
            </a>
          </li>
          <li>
            <a href="<?php echo e(url('main/profil?tab=gaji_pokok')); ?>" class="<?php echo e(($subpage == 'gaji_pokok' ? 'active' : '')); ?>">
              <i class="bx bxs-wallet" style="color:#2980b9"></i>
              <div>Riwayat Gaji</div>
            </a>
          </li>
          <li>
            <a href="<?php echo e(url('main/profil?tab=ubah_password')); ?>" class="<?php echo e(($subpage == 'ubah_password' ? 'active' : '')); ?>">
              <i class="bx bxs-lock-alt" style="color:#f39c12"></i>
              <div>Ubah Password</div>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div class="col">
      <?php echo $__env->make('profil.'.$subpage, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>
  cancel_personal();
  function cancel_personal(){
    $('#personal .show').show();
    $('#personal  .form').hide();
  }
  function edit_personal(){
    $('#personal .show').hide();
    $('#personal .form').show();
  }

  cancel_kontak();
  function cancel_kontak(){
    $('#kontak .show').show();
    $('#kontak  .form').hide();
  }
  function edit_kontak(){
    $('#kontak .show').hide();
    $('#kontak .form').show();
  }
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/profil/index.blade.php ENDPATH**/ ?>