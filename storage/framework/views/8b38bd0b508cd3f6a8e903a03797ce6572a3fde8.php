<?php
  $app='master';
  $page='Data Master';
  $subpage='Rekening Pembayaran';
?>

<?php $__env->startSection('title'); ?>
  Rekening Pembayaran |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <style>
  .logo-wrapper {
    border:2px solid #e2e2e2 ;
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: none;
    margin: 0 10px 0px 0;
    transition: all .3s ease;
    background: whitesmoke;
    padding:5px
  }
  .logo-wrapper img {
    height: 100%;
    width: 100%;
    transition: all .3s ease;
    object-fit: contain;
  }

  .logo-wrapper .upload-button {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    cursor:pointer;
  }

  .logo-wrapper .file-upload{
    opacity: 0;
    pointer-events: none;
    position: absolute;
  }
  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/bank.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Rekening Pembayaran</h4>
          <p class="text-muted m-0">Menampilkan Data Rekening Pembayaran yang digunakan dalam melakukan semua transaksi</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <form action="" method="get">
        <select class="select2 form-control" name="periode" onchange="javascript:submit()">
          <?php $__currentLoopData = $data['metode-pembayaran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($value->id); ?>"><?php echo e($value->metode_pembayaran); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        </form>
      </div>
      <div class="col-md-9">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Pengurus">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="row mt-4">
    <div class="col-auto">
      <form action="<?php echo e(url('master/rekening_pembayaran/proses')); ?>" method="post" enctype="multipart/form-data">
        <?php echo e(csrf_field()); ?>

        <div class="card" style="width:350px;">
          <div class="card-body">
            <h5 id="title"></h5>
            <hr>
            <div class="logo-wrapper" style="width:100px;height:100px" data-tippy-placement="bottom" title="Change Logo">
              <img id="logo_rekening" src="<?php echo e(asset('assets/images/image-default.png')); ?>" alt="" />
              <div class="upload-button" onclick="changeImage('logo')"></div>
              <input class="file-upload" type="file" name="logo" accept="image/*"/>
            </div>
            <div class="form-group mt-3">
              <label>keterangan</label>
              <input type="text" class="form-control mb-3 mt-2" name="keterangan" id="keterangan" autocomplete="off"  >
            </div>
            <div class="form-group">
              <label>Metode Pembayaran</label>
              <select class="select2" name="metode" id="metode" style="width:100%;margin-top:20px">
                <?php $__currentLoopData = $data['metode-pembayaran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value->id); ?>" ><?php echo e($value->metode_pembayaran); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="form-group">
              <label>No. Rekening</label>
              <input type="text" class="form-control" name="no_rekening" id="no_rekening" autocomplete="off" >
            </div>
            <div class="form-group">
              <label>Atas Nama</label>
              <input type="text" class="form-control" name="atas_nama" id="atas_nama" autocomplete="off" >
            </div>
            
          </div>
          <div class="card-footer">
            <input type="hidden" name="id" id="id">
            <div class="pull-right">
              <button type="submit" class="btn btn-primary" id="action"></button>
              <button type="button" class="btn btn-secondary" id="cancel" onclick="add_rekening()" >Cancel</button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="col">
      <?php if(count($data['rekening-pembayaran'])==0): ?>
      <div style="width:100%;text-align:center">
        <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
        <h4 class="mt-2">Data Rekening Pembayaran tidak Ditemukan</h4>
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-middle table-custom">
          <thead>
            <tr>
              <th>Metode<br>Pembayaran</th>
              <th>Nomor<br>Rekening</th>
              <th>Bagan Akun</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $data['rekening-pembayaran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td>
                  <div class="media">
                    <div class="logo-wrapper" style="width:50px;height:50px;background:transparent;border:none">
                      <img src="<?php echo e((!empty($value->logo) ? asset('storage/'.$value->logo) : asset('assets/images/image-default.png') )); ?>" alt="">
                    </div>
                    <div class="media-body align-self-center">
                      <h5 class="text-truncate font-size-14"><?php echo e($value->keterangan); ?></h5>
                      <p class="text-muted mb-0 mt-1 font-size-12"><?php echo e($value->metode_pembayaran); ?></p>
                    </div>
                  </div>
                </td>
                <td>
                  <?php if(!empty($value->no_rekening)): ?>
                  <h6><?php echo e($value->no_rekening); ?></h6>
                  <span><?php echo e($value->atas_nama); ?></span>
                  <?php else: ?>
                  <h6>-</h6>
                  <?php endif; ?>
                </td>
                <td><h6>-</h6></td>
                <td style="width:1px;white-space:nowrap">
                  <div class="text-center">
                    <a href="javascript:;" onclick="edit_rekening(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                    <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                    <form action="<?php echo e(url('master/rekening_pembayaran/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
                      <?php echo e(csrf_field()); ?>

                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?php echo e($value->id); ?>">
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>
  add_rekening();
  function add_rekening(){
    $('#keterangan').val('');
    $('#no_rekening').val('');
    $('#atas_nama').val('');

    $('#metode').val(1);
    $('#metode').select2();

    // $('#status_aktif').val(1);
    // $('#status_aktif').select2();

    $('#id').val(0);
    $('#action').val('add');
    $('#action').html('Tambah');
    $('#title').html('Tambah Rekening');
    $('#cancel').hide();
  }

  function edit_rekening(id){
    $.get("<?php echo e(url('api/find_metode_pembayaran')); ?>/"+id,function(result){
      $('#keterangan').val(result.keterangan);

      $('#metode').val(result.fid_metode_pembayaran);
      $('#metode').select2();

      // $('#status_aktif').val(result.is_active);
      // $('#status_aktif').select2();

      $('#no_rekening').val(result.no_rekening);
      $('#atas_nama').val(result.atas_nama);
      $('#id').val(id);
      $('#action').val('edit');
      $('#action').html('Simpan');
      $('#title').html('Edit Rekening');
      $('#cancel').show();
    });
  }

  function changeImage(target) {
    var readURL = function(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $('#logo_rekening').attr('src', e.target.result);
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/master/rekening_pembayaran/index.blade.php ENDPATH**/ ?>