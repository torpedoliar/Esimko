<?php
  $subpage='Angsuran Belanja';
  $keterangan='Halaman retur barang belanja yang dilakukan oleh anggota';
?>

<?php $__env->startSection('content_belanja'); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('add_js'); ?>
<script>

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main.belanja.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/main/belanja/angsuran/index.blade.php ENDPATH**/ ?>