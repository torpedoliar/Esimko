<?php
  $page='Pinjaman';
  $subpage='Pinjaman';
?>

<?php $__env->startSection('title'); ?>
Pinjaman |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <link href="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')); ?>" rel="stylesheet" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
  <div class="content-breadcrumb mb-2">
    <div class="container-fluid">
      <div class="page-title-box pb-0">
        <div class="media">
          <img src="<?php echo e(asset('assets/images/icon-page/save-money.png')); ?>" class="avatar-md mr-3">
          <div class="media-body align-self-center">
            <h4 class="mb-0 font-size-18">Pinjaman</h4>
            <p class="text-muted m-0">Menampilkan data pengajuan pinjaman yang sudah diinput oleh petugas atau anggota</p>
          </div>
        </div>
      </div>
      <form action="<?php echo e(url('main/transaksi/filter')); ?>" method="post" id="filter_transaksi" class="mt-5">
        <?php ($filter=Session::get('filter_transaksi')); ?>
        <?php echo e(csrf_field()); ?>

        <div class="row">
          <div class="col-md-3">
            <select name="jenis" class="form-control select2" onchange="javascript:submit()">
              <option value="all"  >Semua Jenis</option>
              <?php $__currentLoopData = $data['jenis-transaksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value->id); ?>" <?php echo e((!empty($filter['pinjaman']) ? ($filter['pinjaman']['jenis']==$value->id ? 'selected' : '') : '' )); ?>  ><?php echo e($value->jenis_transaksi); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-md-3">
            <input type="hidden" id="status_id" name="status" value="<?php echo e((!empty($filter['pinjaman']) ? $filter['pinjaman']['status'] : 'all' )); ?>">
            <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
              <option value="#282828" data-id="all">Semua Status</option>
              <?php $__currentLoopData = $data['status-transaksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value->color); ?>" <?php echo e((!empty($filter['pinjaman']) ? ($filter['pinjaman']['status']==$value->id ? 'selected' : '') : '' )); ?>  data-id="<?php echo e($value->id); ?>" ><?php echo e($value->status); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-md-4">
            <div>
              <div class="input-daterange input-group" data-date-format="dd-mm-yyyy" data-provide="datepicker">
                <input type="text" class="form-control" value="<?php echo e((!empty($filter['pinjaman']) ? $filter['pinjaman']['from'] : '' )); ?>" autocomplete="off" id="from" onchange="javascript:submit()" name="from" placeholder="Dari Tanggal" />
                <input type="text" class="form-control" value="<?php echo e((!empty($filter['pinjaman']) ? $filter['pinjaman']['to'] : '' )); ?>" autocomplete="off" id="to" onchange="javascript:submit()" name="to" placeholder="Sampai Tanggal" />
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <a href="<?php echo e(url('main/pinjaman/form')); ?>" class="btn btn-primary btn-block">Formulir Pinjaman</a>
          </div>
        </div>
        <input type="hidden" name="modul" value="pinjaman">
      </form>
    </div>
  </div>
  <div class="container-fluid mt-4 mb-5">
    <div class="row">
      <div class="col">
        <div style="height:100%">
          <?php if(count($data['pinjaman'])==0): ?>
            <div style="width:100%;text-align:center" class="mb-5">
              <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-3" style="width:180px">
              <h5 class="mt-3">Data Pinjaman Tidak Ditemukan</h5>
            </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table table-middle table-custom">
              <thead >
                <tr>
                  <th class="center">Tanggal</th>
                  <th>Jenis Pinjaman</th>
                  <th style="text-align:right">Jumlah<br>Pinjaman<br></th>
                  <th style="text-align:right">Total<br>Angsuran<br></th>
                  <th class="center">Sisa<br>Tenor</th>
                  <th style="text-align:right">Sisa<br>Pinjaman<br></th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php $__currentLoopData = $data['pinjaman']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                    <td class="center" style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')); ?></td>
                    <td style="font-weight:500"><?php echo e($value->jenis_transaksi); ?></td>
                    <td style="text-align:right"><?php echo e(number_format(str_replace('-','',$value->nominal),0,',','.')); ?></td>
                    <td style="text-align:right"><?php echo e(number_format(str_replace('-','',$value->total_angsuran),0,',','.')); ?></td>
                    <td class="center"><?php echo e($value->sisa_tenor); ?> dari <?php echo e($value->tenor); ?></td>
                    <td style="text-align:right"><?php echo e(number_format(str_replace('-','',$value->sisa_pinjaman),0,',','.')); ?></td>
                    <td style="width:1px;white-space:nowrap">
                      <div class="text-center">
                        <a href="<?php echo e(url('main/pinjaman/detail?id='.$value->id)); ?>" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tbody>
            </table>
          </div>
          <div class="mb-4">
            <?php echo e($data['pinjaman']->links('include.pagination', ['pagination' => $data['pinjaman']] )); ?>

          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-auto">
        <div style="border-left:1px solid #dedede;padding:20px 20px;height:100%;width:250px" class="mb-4">
          <?php $__currentLoopData = $data['jenis-transaksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="form-group">
              <label>Sisa <?php echo e($value->jenis_transaksi); ?></label>
              <div class="font-size-13">Rp <?php echo e(number_format($value->sisa_pinjaman,0,'.',',')); ?></div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <div class="form-group">
            <label>Total Sisa Pinjaman</label>
            <div class="font-size-13">Rp <?php echo e(number_format($data['total-sisa'],0,'.',',')); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script src="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')); ?>"></script>
  <script src="<?php echo e(asset('assets/js/pages/form-advanced.init.js')); ?>"></script>
  <script>
  function formatStatus(status) {
    var $status = $(
      '<span style="display:flex;align-items:center;"><div class="indikator-status mr-2" style="background:'+status.id+'"></div>'+status.text+'</span>'
    );
    return $status;
  };

  $(".select2-status").select2({
    templateResult: formatStatus
  });

  function pilih_status(){
    let id = $('#status_color').find('option:selected').attr('data-id');
    $('#status_id').val(id);
    $('#filter_transaksi').submit();
  }
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/main/transaksi/pinjaman/index.blade.php ENDPATH**/ ?>