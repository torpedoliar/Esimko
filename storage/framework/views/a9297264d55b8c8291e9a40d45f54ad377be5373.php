<?php
  $app='master';
  $page='Data Pengurus';
  $subpage='Data Pengurus';
?>

<?php $__env->startSection('title'); ?>
  Data Pengurus |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <style>
  .list-anggota{
    padding-bottom:10px;
    border-bottom: 1px solid #f2f2f2;
    margin-top:10px;
    cursor: pointer;
  }
  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="<?php echo e(asset('assets/images/icon-page/organization-chart.png')); ?>" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Data Pengurus</h4>
        <p class="text-muted m-0">Formulir pengisian data pengurus yang dilakukan oleh petugas</p>
      </div>
    </div>
  </div>
  <form action="<?php echo e(url('master/pengurus/proses')); ?>" method="post" enctype="multipart/form-data">
    <?php echo e(csrf_field()); ?>

    <div class="card">
      <div class="card-header">
        <h5><?php echo e(($action=='add' ? 'Tambah' : 'Edit')); ?> Data Pengurus</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-5">
            <div style="border:#dfe4e9 dashed 2px ;padding:20px">
              <h5 class="mb-3"># Identitas Anggota</h5>
              <div class="row">
                <div class="col-auto">
                  <div class="avatar-wrapper" style="height:100px;width:100px">
                    <img src="<?php echo e(asset('assets/images/user-avatar-placeholder.png')); ?>" alt="" />
                  </div>
                </div>
                <div class="col">
                  <div class="list-content">
                    <span>No. Anggota</span>
                    <div id="no_anggota" class="info-content"><?php echo (!empty($data['pengurus']) ? $data['pengurus']->no_anggota :'<hr>'); ?></div>
                  </div>
                  <div class="list-content">
                    <span>Nama Lengkap</span>
                    <div id="nama_lengkap" class="info-content"><?php echo (!empty($data['pengurus']) ? $data['pengurus']->nama_lengkap :'<hr>'); ?></div>
                  </div>
                </div>
              </div>
              <input type="hidden" name="no_anggota" value="<?php echo e((!empty($data['pengurus']) ? $data['pengurus']->no_anggota : '')); ?>" id="fid_anggota">
              <button type="button" onclick="pilih_anggota('show')" class="btn btn-secondary btn-block mt-3">PILIH ANGGOTA</button>
            </div>
          </div>
          <div class="col-md-7">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Periode</label>
                  <select name="periode" id="periode" class="form-control select2" >
                    <option value="new">Periode Baru</option>
                    <?php $__currentLoopData = $data['pilih-periode']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value->id); ?>" <?php echo e((!empty($data['pengurus']) && $data['pengurus']->fid_periode == $value->id ? 'selected' : '')); ?>><?php echo e($value->periode_awal); ?> s/d <?php echo e($value->periode_akhir); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Range Periode</label>
                  <div>
                    <div class="input-group">
                      <input type="text" class="form-control center" value="<?php echo e((!empty($data['pengurus']) ? $data['pengurus']->periode_awal : date('Y') )); ?>" autocomplete="off" id="periode_awal" name="awal" placeholder="Periode Awal" />
                      <input type="text" class="form-control center" value="<?php echo e((!empty($data['pengurus']) ? $data['pengurus']->periode_akhir : date('Y') )); ?>" autocomplete="off" id="periode_akhir" name="akhir" placeholder="Periode Akhir" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tanggal</label>
                  <input type="text" name="tanggal" autocomplete="off" value="<?php echo e((!empty($data['pengurus']) ? \App\Helpers\GlobalHelper::dateFormat($data['pengurus']->tanggal,'d-m-Y') : date('d-m-Y'))); ?>" class="datepicker form-control">
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Jabatan</label>
                  <select name="jabatan" class="form-control select2" >
                    <?php $__currentLoopData = $data['pilih-jabatan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value->id); ?>" ><?php echo e($value->nama_jabatan); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status pengurus</label>
                  <select name="status" class="form-control select2" >
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
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
          <a class="btn btn-secondary" href="<?php echo e(url('master/pengurus')); ?>" >Kembali</a>
          <button class="btn btn-primary" type="submit"><?php echo e(($action=='add' ? 'Tambah' : 'Simpan')); ?></button>
        </div>
      </div>
    </div>
  </form>
</div>
<div id="modal-anggota" class="modal fade right">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Pilih Anggota</h5>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <input type="text" class="form-control" value="" id="search" name="search" placeholder="Cari Anggota">
          <div class="input-group-append">
            <button class="btn btn-dark" onclick="search_anggota()">Search</button>
          </div>
        </div>
        <div id="loading"><img src="<?php echo e(asset('assets/images/loading.gif')); ?>" style="width:100px"></div>
        <div id="list-anggota" ></div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
<script>
function search_anggota(){
  var search = $('#search').val();
  if(search !== ''){ search = '/'+search }
  else{ search = '/all'}
  $('#loading').show();
  $('#list-anggota').hide();
  $.get("<?php echo e(url('api/get_anggota/aktif/')); ?>"+search,function (result) {
    $('#list-anggota').html('');
    $.each(result,function(i,value){
    $('#list-anggota').append('<div class="list-anggota" onclick="pilih_anggota('+value.id+')">'+
      '<div class="media">'+
        '<div class="avatar-thumbnail avatar-sm rounded-circle mr-2">'+
          '<img style="margin-right:10px;" src="'+value.avatar+'" alt="" style="max-width:none" class="rounded-circle">'+
        '</div>'+
        '<div class="media-body align-self-center" >'+
          '<p class="text-muted mb-0">No. '+value.no_anggota+'</p>'+
          '<h5 class="text-truncate font-size-16">'+value.nama_lengkap+'</h5>'+
        '</div>'+
      '</div>'+
    '</div>');
    });
    $('#loading').hide();
    $('#list-anggota').show();
  });
};

function pilih_anggota(id){
  if(id=='show'){
    search_anggota();
    $('#modal-anggota').modal('show');
  }
  else{
    $.get("<?php echo e(url('api/find_anggota')); ?>/"+id,function(result){
      $('#user_akses').val(result.user_akses);
      $('#nama_lengkap').html(result.nama_lengkap);
      $('#no_anggota').html(result.no_anggota);
      $('#fid_anggota').val(result.no_anggota);
      $('#modal-anggota').modal('hide');
    });
  }
}

$("#periode").change(function(){
  let id =$(this).val();
  $.get("<?php echo e(url('api/find_periode_pengurus')); ?>/"+id,function(result){
    $('#periode_awal').val(result.periode_awal);
    $('#periode_akhir').val(result.periode_akhir);
  });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/master/pengurus/form.blade.php ENDPATH**/ ?>