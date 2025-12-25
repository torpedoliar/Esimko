<?php
  $page='Angsuran';
  $subpage='Angsuran';
?>

<?php $__env->startSection('title'); ?>
Angsuran |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
  <link href="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')); ?>" rel="stylesheet" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
  <div class="content-breadcrumb mb-2">
    <div class="container-fluid">
      <div class="page-title-box pb-0">
        <div class="media">
          <img src="<?php echo e(asset('assets/images/icon-page/pay-day.png')); ?>" class="avatar-md mr-3">
          <div class="media-body align-self-center">
            <h4 class="mb-0 font-size-18">Angsuran</h4>
            <p class="text-muted m-0">Menampilkan data angsuran pinjaman yang sudah diinput oleh petugas atau anggota</p>
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
              <option value="<?php echo e($value->id); ?>" <?php echo e((!empty($filter['angsuran']) ? ($filter['angsuran']['jenis']==$value->id ? 'selected' : '') : '' )); ?>  ><?php echo e($value->jenis_transaksi); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          
        </div>
        <input type="hidden" name="modul" value="angsuran">
      </form>
    </div>
  </div>
  <div class="container-fluid mt-4 mb-5">
    <div class="row">
      <div class="col">
        <div style="height:100%">
          <?php if(count($data['angsuran'])==0): ?>
            <div style="width:100%;text-align:center" class="mb-5">
              <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-3" style="width:180px">
              <h5 class="mt-3">Data Angsuran Tidak Ditemukan</h5>
            </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table table-middle table-custom">
              <thead >
                <tr>
                  <th class="center">Angsuran Ke</th>
                  <th class="center">Bulan</th>
                  <th>Jenis Pinjaman</th>
                  <th style="text-align:right">Angsuran<br>Pokok<br></th>
                  <th style="text-align:right">Angsuran<br>Bunga<br></th>
                  <th style="text-align:right">Total<br>Angsuran<br></th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php $__currentLoopData = $data['angsuran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                    <td class="center" style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e($value->angsuran_ke); ?></td>
                    <td class="center"><?php echo e(\App\Helpers\GlobalHelper::nama_bulan($value->bulan)); ?></td>
                    <td style="font-weight:500"><?php echo e($value->jenis_transaksi); ?></td>
                    <td style="text-align:right"><?php echo e(number_format(str_replace('-','',$value->angsuran_pokok),0,',','.')); ?></td>
                    <td style="text-align:right"><?php echo e(number_format(str_replace('-','',$value->angsuran_bunga),0,',','.')); ?></td>
                    <td style="text-align:right"><?php echo e(number_format(str_replace('-','',$value->total_angsuran),0,',','.')); ?></td>
                    
                    <td style="width:1px;white-space:nowrap">
                      <div class="text-center">
                        <a href="<?php echo e(url('main/angsuran/detail?id='.$value->id)); ?>" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tbody>
            </table>
          </div>
          <div class="mb-4">
            <?php echo e($data['angsuran']->links('include.pagination', ['pagination' => $data['angsuran']] )); ?>

          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-auto">
        <div style="border-left:1px solid #dedede;padding:20px 20px;height:100%;width:250px" class="mb-4">
          <?php $__currentLoopData = $data['jenis-transaksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="form-group">
              <label>Angsuran <?php echo e(str_replace('Pinjaman',' ',$value->jenis_transaksi)); ?></label>
              <div class="font-size-13">Rp <?php echo e(number_format($value->angsuran_pinjaman,0,',','.')); ?></div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <div class="form-group">
            <label>Total Angsuran</label>
            <div class="font-size-13">Rp <?php echo e(number_format($data['total-angsuran'],0,',','.')); ?></div>
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

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/main/transaksi/angsuran/index.blade.php ENDPATH**/ ?>