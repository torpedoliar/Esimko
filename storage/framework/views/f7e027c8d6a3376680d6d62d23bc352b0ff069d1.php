<div class="card" style="min-height:calc(100vh - 400px)">
  <div class="card-header">
    <h5>Ubah Password</h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-7" style="text-align:center;">
        <img src="<?php echo e(asset('assets/images/icon-page/password.png')); ?>" class="mt-4" style="width:150px">
        <p class="mt-3 text-muted font-size-15">Silahkan ganti password anda secara rutin,<br>agar akun anda semakin aman</p>
      </div>
      <div class="col-md-5">
        <form action="<?php echo e(url('main/profil/ubah_password')); ?>" method="post">
          <?php echo e(csrf_field()); ?>

          <div class="form-group">
            <label>Password Lama</label>
            <input type="password" class="form-control" name="password_lama">
          </div>
          <div class="form-group">
            <label>Password Baru</label>
            <input type="password" class="form-control" name="password_baru">
          </div>
          <div class="form-group">
            <label>Password Baru</label>
            <input type="password" class="form-control" name="ulangi_password_baru">
          </div>
          <button class="btn btn-primary btn-block">Ubah Password</button>
        </form>
      </div>
    </div>

  </div>
</div>
<?php /**PATH /var/www/html/resources/views/profil/ubah_password.blade.php ENDPATH**/ ?>