<?php
  $app='manajemen_barang';
  $page='Manajemen Stok';
  $subpage='Data Supplier';
?>

<?php $__env->startSection('title'); ?>
  Data Supplier |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="<?php echo e(asset('assets/images/icon-page/courier.png')); ?>" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Data Supplier</h4>
          <p class="text-muted m-0">Menampilkan data supplier yang mensuplai persedian barang toko</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-9">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Search Supplier">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <button type="button" class="btn btn-primary btn-block" onclick="add_supplier()">Tambah Supplier</button>
      </div>
    </div>
  </div>
  <?php if(count($data['supplier'])==0): ?>
  <div style="width:100%;text-align:center">
    <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
    <h4 class="mt-2">DATA SUPPLIER TIDAK DITEMUKAN</h4>
  </div>
  <?php else: ?>
  <div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th>Nama Supplier</th>
          <th>Informasi Kontak</th>
          <th>Alamat</th>
          <th>Rekening</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $data['supplier']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td>
            <h6><?php echo e($value->nama_supplier); ?></h6>
            <?php echo e($value->contact_person); ?>

          </td>
          <td>
            <?php if($value->email==NULL && $value->no_handphone==NULL ): ?>
              BELUM DIISI
            <?php else: ?>
            <div><?php echo e($value->email); ?></div>
            <div><?php echo e($value->no_handphone); ?></div>
            <?php endif; ?>
          </td>
          <td><?php echo e(($value->alamat==NULL ? 'BELUM DIISI' : $value->alamat )); ?></td>
          <td>
            <?php if($value->no_rekening==NULL && $value->nama_bank==NULL ): ?>
              BELUM DIISI
            <?php else: ?>
              <div><?php echo e($value->no_rekening); ?></div>
              <div><?php echo e($value->nama_bank); ?></div>
            <?php endif; ?>
          </td>
          <td style="width:1px;white-space:nowrap">
            <div class="text-center">
              <a href="javascript:;" onclick="edit_supplier(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
              <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
              <form action="<?php echo e(url('manajemen_stok/supplier/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
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
  <div class="mb-4">
    <?php echo e($data['supplier']->links('include.pagination', ['pagination' => $data['supplier']] )); ?>

  </div>
  <?php endif; ?>
</div>
<div class="modal fade" id="form-supplier">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?php echo e(url('manajemen_stok/supplier/proses')); ?>" method="post">
        <?php echo e(csrf_field()); ?>

        <div class="modal-body">
          <div class="form-group">
            <label>Nama Supplier</label>
            <input type="text" class="form-control" name="nama_supplier" id="nama_supplier" required >
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Contact Person</label>
                <input type="text" class="form-control" name="contact_person" id="contact_person" required >
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>No. Handphone</label>
                <input type="text" class="form-control" name="no_handphone" id="no_handphone" required >
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Website</label>
                <input type="text" class="form-control" name="website" id="website" >
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Email</label>
                <input type="text" class="form-control" name="email" id="email" >
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>No Rekening</label>
            <input type="text" class="form-control" name="no_rekening" id="no_rekening" >
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nama Bank</label>
                <input type="text" class="form-control" name="nama_bank" id="nama_bank" >
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Atas Nama</label>
                <input type="text" class="form-control" name="atas_nama" id="atas_nama" >
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Alamat</label>
            <textarea class="form-control" name="alamat" id="alamat" style="height:100px" required ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id" id="id">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>
  function add_supplier(){
    $('#nama_supplier').val('');
    $('#contact_person').val('');
    $('#email').val('');
    $('#no_handphone').val('');
    $('#alamat').val('');
    $('#website').val('');
    $('#no_rekening').val('');
    $('#nama_bank').val('');
    $('#atas_nama').val('');
    $('#id').val(0);
    $('#action').val('add');
    $('#title').html('Tambah Supplier');
    $('#form-supplier').modal('show');
  }

  function edit_supplier(id){
    $.get("<?php echo e(url('api/find_supplier')); ?>/"+id,function(result){
      $('#nama_supplier').val(result.nama_supplier);
      $('#contact_person').val(result.contact_person);
      $('#email').val(result.email);
      $('#no_handphone').val(result.no_handphone);
      $('#alamat').val(result.alamat);
      $('#website').val(result.website);
      $('#no_rekening').val(result.no_rekening);
      $('#nama_bank').val(result.nama_bank);
      $('#atas_nama').val(result.atas_nama);
      $('#id').val(id);
      $('#action').val('edit');
      $('#title').html('Edit Supplier');
      $('#form-supplier').modal('show');
    });
  }
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/manajemen_stok/supplier/index.blade.php ENDPATH**/ ?>