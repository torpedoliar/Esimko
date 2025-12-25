<?php
  $app='master';
  $page='Data Anggota';
  $subpage='Data Anggota';
?>

<?php $__env->startSection('title'); ?>
  Data Anggota |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="<?php echo e(asset('assets/images/icon-page/profile.png')); ?>" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Data Anggota</h4>
        <p class="text-muted m-0">Formulir pendaftaran anggota koperasi yang dilakukan oleh petugas</p>
      </div>
    </div>
  </div>
  <form action="<?php echo e(url('anggota/proses')); ?>" style="margin-top:30px" method="post" enctype="multipart/form-data">
    <?php echo e(csrf_field()); ?>

    <div class="card">
      <div class="card-header">
        <h5><?php echo e(($action=='add' ? 'Tambah' : 'Edit')); ?> Anggota</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-auto">
            <div class="avatar-wrapper" data-tippy-placement="bottom" title="Change Avatar" style="width:225px;height:225px">
              <img id="modal_avatar" src="<?php echo e((!empty($data['anggota']->avatar) ? asset('storage/'.$data['anggota']->avatar) : asset('assets/images/user-avatar-placeholder.png') )); ?>" alt="" />
              <div class="upload-button" onclick="changeImage('avatar')"></div>
              <input class="file-upload" type="file" name="avatar" accept="image/*"/>
            </div>
          </div>
          <div class="col">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>No. Anggota <br> (No. Terakhir <?php echo e($data['no_anggota'][0] . ' | ' . $data['no_anggota'][1]); ?>)</label>
                  <input type="text" class="form-control" name="no_anggota" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->no_anggota : '')); ?>"  autocomplete="off"  >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>No. KTP</label>
                  <input type="text" class="form-control" name="no_ktp" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->no_ktp : '')); ?>"  autocomplete="off" >
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Lengkap</label>
                  <input type="text" class="form-control" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->nama_lengkap : '')); ?>" name="nama_lengkap" autocomplete="off" >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Tempat Lahir</label>
                  <input type="text" class="form-control" name="tempat_lahir" autocomplete="off" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->tempat_lahir : '')); ?>" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Tanggal Lahir</label>
                  <input type="text" class="datepicker form-control" value="<?php echo e((!empty($data['anggota']) ? \App\Helpers\GlobalHelper::dateFormat($data['anggota']->tanggal_lahir,'d-m-Y') : '')); ?>" name="tanggal_lahir" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Jenis Kelamin</label>
                  <select class="select2" name="jenis_kelamin" style="width:100%">
                    <option value="L" >Laki-Laki</option>
                    <option value="P" >Perempuan</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Nama Panggilan</label>
                  <input type="text" class="form-control" name="nama_panggilan" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->nama_panggilan : '')); ?>"  autocomplete="off"  >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>No. HIRS</label>
                  <input type="text" class="form-control" name="no_hirs" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->no_hirs : '')); ?>"  autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>ID. Karyawan</label>
                  <input type="text" class="form-control" name="id_karyawan" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->id_karyawan : '')); ?>" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Lokasi Kerja</label>
                  <select class="select2 form-control" name="lokasi_kerja">
                    <option value="SJA-1">SJA 1</option>
                    <option value="SJA-3">SJA 3</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Level Jabatan</label>
                  <input type="text" class="form-control" name="level" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->level : '')); ?>" autocomplete="off" >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Tanggal Bergabung</label>
                  <input type="text" class="datepicker form-control" name="tanggal_bergabung" value="<?php echo e((!empty($data['anggota']) ? \App\Helpers\GlobalHelper::dateFormat($data['anggota']->tanggal_bergabung,'d-m-Y') : '')); ?>" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-9">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Divisi</label>
                      <input type="text" class="form-control" name="divisi" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->divisi : '')); ?>" autocomplete="off" >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Bagian</label>
                      <input type="text" class="form-control" name="bagian" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->bagian : '')); ?>" autocomplete="off" >
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Email</label>
                  <input type="text" class="form-control" name="email" autocomplete="off" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->email : '')); ?>" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>No Handphone</label>
                  <input type="text" class="form-control" name="no_handphone" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->no_handphone : '')); ?>" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>No. Rekening</label>
                  <input type="text" class="form-control" name="no_rekening" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->no_rekening : '')); ?>" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Nama Bank</label>
                  <input type="text" class="form-control" name="nama_bank" value="<?php echo e((!empty($data['anggota']) ? $data['anggota']->nama_bank : '')); ?>"  autocomplete="off" >
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Alamat</label>
              <textarea class="form-control" name="alamat" autocomplete="off" style="height:70px" ><?php echo e((!empty($data['anggota']) ? $data['anggota']->alamat : '')); ?></textarea>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tambahan Akses</label>
                  <select class="select2" name="hak_akses[]" multiple style="width:100%"  >
                    <?php $__currentLoopData = $data['hak-akses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value->id); ?>" <?php echo e($value->selected); ?> ><?php echo e($value->hak_akses); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Password</label>
                  <input type="text" class="form-control" name="password" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Status Anggota</label>
                  <select class="select2" name="status_anggota" style="width:100%">
                    <?php $__currentLoopData = $data['status-anggota']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value->id); ?>" <?php echo e((!empty($data['anggota']) && $data['anggota']->fid_status == $value->id ? 'selected' : '')); ?> ><?php echo e($value->status_anggota); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <input type="hidden" name="action" value="<?php echo e($action); ?>">
        <input type="hidden" name="id" value="<?php echo e($id); ?>">
        <div class="pull-right">
          <a class="btn btn-secondary" href="<?php echo e(url('anggota')); ?>" >Kembali</a>
          <button class="btn btn-primary" type="submit"><?php echo e(($action=='add' ? 'Tambah' : 'Simpan')); ?></button>
        </div>
      </div>
    </div>
  </form>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
<script>
function changeImage(target) {
  var readURL = function(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('.'+target+'-wrapper img').attr('src', e.target.result);
      };
      reader.readAsDataURL(input.files[0]);
    }
  };

  $(".file-upload").on('change', function(){
    readURL(this);
  });
  $(".file-upload").click();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/anggota/form.blade.php ENDPATH**/ ?>