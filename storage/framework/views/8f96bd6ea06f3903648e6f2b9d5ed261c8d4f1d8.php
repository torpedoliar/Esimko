<?php
  $app='master';
  $page='Pengaturan';
  $subpage='Otoritas Modul';
?>

<?php $__env->startSection('title'); ?>
  Otoritas Modul |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="<?php echo e(asset('assets/images/icon-page/account.png')); ?>" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Otoritas Modul</h4>
        <p class="text-muted m-0">Menampilkan otoritas modul dari setiap user akses</p>
      </div>
    </div>
  </div>
  <div class="card mt-3">
    <div class="card-header">
      <div class="row">
        <div class="col-md-4">
          <form action="" method="get">
            <select id="hak_akses"  class="select2" name="hak_akses" style="width:100%" onchange="javascript:submit()">
              <?php $__currentLoopData = $data['hak-akses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value->id); ?>" <?php echo e(($value->id==$hak_akses ? 'selected' : '')); ?>><?php echo e($value->hak_akses); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </form>
        </div>
      </div>
    </div>
    <div class="card-body">
      <form action="<?php echo e(url('pengaturan/otoritas_user/proses')); ?>" method="POST">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th width="30px">No</th>
              <th colspan="2">Nama Modul</th>
              <th style="width:80px;text-align:center;line-height:25px">View<br><input type="checkbox" id="check_all_view"></th>
              <th style="width:80px;text-align:center;line-height:25px">Insert<br><input type="checkbox" id="check_all_insert"></th>
              <th style="width:80px;text-align:center;line-height:25px">Update<br><input type="checkbox" id="check_all_update"></th>
              <th style="width:80px;text-align:center;line-height:25px">Delete<br><input type="checkbox" id="check_all_delete"></th>
              
              <th style="width:80px;text-align:center;line-height:25px">Print<br><input type="checkbox" id="check_all_print"></th>
              <th style="width:80px;text-align:center;line-height:25px">Verified<br><input type="checkbox" id="check_all_verified"></th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $data['modul']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php ($otoritas=$value->otoritas); ?>
              <tr style="background:whitesmoke">
                <th style="text-align:left"><?php echo e($key+1); ?></th>
                <th colspan="2"><?php echo e($value->nama_modul); ?><input type="hidden" name="id[]" value="<?php echo e($value->id); ?>" ></th>
                <th style="text-align:center"><input type="checkbox" <?php if($otoritas['view'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_view" name="view[<?php echo e($value->id); ?>]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" <?php if($otoritas['insert'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_insert" name="insert[<?php echo e($value->id); ?>]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" <?php if($otoritas['update'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_update" name="update[<?php echo e($value->id); ?>]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" <?php if($otoritas['delete'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_delete" name="delete[<?php echo e($value->id); ?>]" value="Y" ></th>
                
                <th style="text-align:center"><input type="checkbox" <?php if($otoritas['print'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_print" name="print[<?php echo e($value->id); ?>]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" <?php if($otoritas['verified'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_verified" name="verified[<?php echo e($value->id); ?>]" value="Y" ></th>
              </tr>
              <?php $__currentLoopData = $value->submodul; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key2 => $value2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($otoritas=$value2->otoritas); ?>
                <tr>
                  <td style="text-align:left"><?php echo e($key+1); ?>.<?php echo e($key2+1); ?></td>
                  <td colspan="2"><?php echo e($value2->nama_modul); ?><input type="hidden" name="id[]" value="<?php echo e($value2->id); ?>" ></td>
                  <th style="text-align:center"><input type="checkbox" <?php if($otoritas['view'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_view" name="view[<?php echo e($value2->id); ?>]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" <?php if($otoritas['insert'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_insert" name="insert[<?php echo e($value2->id); ?>]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" <?php if($otoritas['update'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_update" name="update[<?php echo e($value2->id); ?>]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" <?php if($otoritas['delete'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_delete" name="delete[<?php echo e($value2->id); ?>]" value="Y" ></th>
                  
                  <th style="text-align:center"><input type="checkbox" <?php if($otoritas['print'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_print" name="print[<?php echo e($value2->id); ?>]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" <?php if($otoritas['verified'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_verified" name="verified[<?php echo e($value2->id); ?>]" value="Y" ></th>
                </tr>
                <?php $__currentLoopData = $value2->submodul; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key3 => $value3): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php ($otoritas=$value3->otoritas); ?>
                  <tr>
                    <td></td>
                    <td style="text-align:left;width:1px;white-space:nowrap"><?php echo e($key+1); ?>.<?php echo e($key2+1); ?>.<?php echo e($key3+1); ?></td>
                    <td><?php echo e($value3->nama_modul); ?><input type="hidden" name="id[]" value="<?php echo e($value3->id); ?>" ></td>
                    <th style="text-align:center"><input type="checkbox" <?php if($otoritas['view'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_view" name="view[<?php echo e($value3->id); ?>]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" <?php if($otoritas['insert'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_insert" name="insert[<?php echo e($value3->id); ?>]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" <?php if($otoritas['update'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_update" name="update[<?php echo e($value3->id); ?>]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" <?php if($otoritas['delete'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_delete" name="delete[<?php echo e($value3->id); ?>]" value="Y" ></th>
                    
                    <th style="text-align:center"><input type="checkbox" <?php if($otoritas['print'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_print" name="print[<?php echo e($value3->id); ?>]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" <?php if($otoritas['verified'] == 'Y'): ?> checked <?php endif; ?> class="checkbox_verified" name="verified[<?php echo e($value3->id); ?>]" value="Y" ></th>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="hak_akses" value="<?php echo e($hak_akses); ?>">
  			<button type="submit" class="btn btn-block btn-primary" >SIMPAN</button>
		  </form>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
  <script>
    $("#check_all_view").click(function(){
      $('.checkbox_view').not(this).prop('checked', this.checked);
    });
    $("#check_all_insert").click(function(){
      $('.checkbox_insert').not(this).prop('checked', this.checked);
    });
    $("#check_all_update").click(function(){
      $('.checkbox_update').not(this).prop('checked', this.checked);
    });
    $("#check_all_delete").click(function(){
      $('.checkbox_delete').not(this).prop('checked', this.checked);
    });
    // $("#check_all_all_user").click(function(){
    //   $('.checkbox_all_user').not(this).prop('checked', this.checked);
    // });
    $("#check_all_print").click(function(){
      $('.checkbox_print').not(this).prop('checked', this.checked);
    });
    $("#check_all_verified").click(function(){
      $('.checkbox_verified').not(this).prop('checked', this.checked);
    });
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pengaturan/otoritas_user/index.blade.php ENDPATH**/ ?>