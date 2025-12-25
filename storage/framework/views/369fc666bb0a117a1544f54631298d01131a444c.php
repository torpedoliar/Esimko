<?php
  $app='master';
  $page='Syarat dan Ketentuan';
  $jenis_title=array('setoran'=>'Setoran Simpanan','penarikan'=>'Penarikan Simpanan','pinjaman'=>'Pengajuan Pinjaman');
  $subpage=$jenis_title[$jenis];
?>

<?php $__env->startSection('title'); ?>
  Syarat dan Ketentuan |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <style>

  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="content-breadcrumb mb-2">
  <div class="container-fluid">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/book.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Syarat dan Ketentuan</h4>
          <p class="text-muted m-0">Menampilkan Syarat dan Ketentuan dari transaksi <?php echo e($jenis_title[$jenis]); ?></p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-9">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="" name="search" placeholder="Cari Data Uraian">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <button type="button" class="btn btn-primary btn-block" onclick="add_syarat()" >Tambah</button>
      </div>
    </div>
  </div>
</div>
<div class="container-fluid">
  <?php if(count($data['syarat_ketentuan'])==0): ?>
  <div style="width:100%;text-align:center">
    <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
    <h4 class="mt-2">Syarat dan Ketentuan tidak Ditemukan</h4>
  </div>
  <?php else: ?>
  <table class="table table-middle table-custom">
    <tbody>
      <?php $__currentLoopData = $data['syarat_ketentuan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td style="width:1px;white-space:nowrap"><?php echo e($key+1); ?></td>
          <td><?php echo e($value->uraian); ?></td>
          <td style="width:1px;white-space:nowrap">
            <div class="text-center">
              <a href="javascript:;" onclick="edit_syarat(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
              <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
              <form action="<?php echo e(url('master/syarat_ketentuan/'.$jenis.'/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
                <?php echo e(csrf_field()); ?>

                <input type="hidden" name="id" value="<?php echo e($value->id); ?>">
                <input type="hidden" name="action" value="delete">
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<div class="modal fade" id="form-syarat">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="title"></h5>
          <div id="title-jenis" style="color:#2aab89;font-size:14px"></div>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?php echo e(url('master/syarat_ketentuan/'.$jenis.'/proses')); ?>" method="post">
        <?php echo e(csrf_field()); ?>

        <div class="modal-body">
          <div class="form-group">
            <label>Syarat dan Ketentuan</label>
            <textarea name="uraian" id="uraian" class="form-control" style="height:200px"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id" id="id">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button class="btn btn-primary"  id="action" name="action">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>
  function add_syarat(){
    $('#uraian').val('');
    $('#id').val(0);
    $('#action').val('add');
    $('#title').html('Tambah Syarat dan Ketentuan');
    $('#title-jenis').html('<?php echo e($jenis_title[$jenis]); ?>');
    $('#form-syarat').modal('show');
  }

  function edit_syarat(id){
    $.get("<?php echo e(url('api/find_syarat_ketentuan')); ?>/"+id,function(result){
      $('#uraian').val(result.uraian);
      $('#id').val(id);
      $('#action').val('edit');
      $('#title').html('Edit Syarat dan Ketentuan');
      $('#title-jenis').html('<?php echo e($jenis_title[$jenis]); ?>');
      $('#form-syarat').modal('show');
    });
  }
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/master/syarat_ketentuan/index.blade.php ENDPATH**/ ?>