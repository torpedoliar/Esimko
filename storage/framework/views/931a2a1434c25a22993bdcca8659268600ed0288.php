
<?php
  if($pagination->lastPage() <= 10){
      $awal = 1;
      $akhir = $pagination->lastPage();
      $shortAwal = false;
      $shortAkhir = false;

  }
  else{
      if(($pagination->currentPage()+5) >= $pagination->lastPage()){
          $awal = $pagination->lastPage()-5;
          $akhir = $pagination->lastPage();
          $shortAwal = true;
          $shortAkhir = false;
      }elseif($pagination->currentPage() > 5){
          $awal = $pagination->currentPage()-2;
          $akhir = $pagination->currentPage()+2;
          $shortAwal = true;
          $shortAkhir = true;
      }else{
          $awal = 1;
          $akhir = 5;
          $shortAwal = false;
          $shortAkhir = true;
      }
  }

?>
<div class="row">
    <div class="col-sm-6">
        <ul class="pagination pagination-primary">
            <li class="page-item"><a <?php if($pagination->currentPage() != 1): ?> href="<?php echo e($pagination->url(1)); ?>" <?php endif; ?> class="page-link"><i class="fa fa-angle-double-left"></i></a></li>
            <li class="page-item"><a <?php if($pagination->currentPage() != 1): ?> href="<?php echo e($pagination->url(($pagination->currentPage()-1))); ?>" <?php endif; ?> class="page-link"><i class="fa fa-angle-left"></i></a></li>

            <?php if($shortAwal == true): ?>
              <li class="page-item"><a class="page-link">...</a></li>
            <?php endif; ?>
            <?php for($i=$awal; $i <= $akhir; $i++): ?>
                <li class="page-item <?php if($pagination->currentPage()==$i): ?> active <?php endif; ?>"><a href="<?php echo e($pagination->url($i)); ?>" class="page-link"><?php echo e($i); ?></a></li>
            <?php endfor; ?>
            <?php if($shortAkhir == true): ?>
                <li class="page-item"><a class="page-link">...</a></li>
            <?php endif; ?>

            <li class="page-item"><a <?php if($pagination->currentPage() != $pagination->lastPage()): ?> href="<?php echo e($pagination->url(($pagination->currentPage()+1))); ?>" <?php endif; ?> class="page-link"><i class="fa fa-angle-right"></i></a></li>
            <li class="page-item"><a <?php if($pagination->currentPage() != $pagination->lastPage()): ?> href="<?php echo e($pagination->url($pagination->lastPage())); ?>" <?php endif; ?> class="page-link"><i class="fa fa-angle-double-right"></i></a></li>
        </ul>
    </div>
    <div class="col-sm-6 text-right">
        <p style="margin-bottom: 0;margin-top: 10px;">Page <?php echo e($pagination->currentPage()); ?> of <?php echo e($pagination->lastPage()); ?> from <?php echo e($pagination->total()); ?> entries</p>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/include/pagination.blade.php ENDPATH**/ ?>