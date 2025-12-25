<?php
  $subpage='Pilih Produk';
  $keterangan='Silahkan melihat dan memilih produk yang dijual di toko kami';
?>

<?php $__env->startSection('content_belanja'); ?>
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-md-9">
        <form action="" method="get">
          <input type="hidden" name="kategori" value="<?php echo e($kategori); ?>">
          <div class="input-group">
            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Produk">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary btn-block" data-toggle="modal" data-target='#filter-barang' >Filter Barang</button>
      </div>
    </div>
  </div>
</div>
<div class="row mt-4">
  <?php $__currentLoopData = $data['produk']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="col-xl-3 col-sm-4 col-6">
    <a href="<?php echo e(url('main/belanja/produk/detail?id='.$value->kode)); ?>">
      <div class="card">
        <div class="produk">
          <img class="card-img-top img-fluid" src="<?php echo e((!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg'))); ?>">
          <div class="card-body">
            <h6 class="title"><a href="" class="text-secondary"><?php echo e($value->nama_produk); ?></a></h6>
            <h6 class="price mt-2">Rp <?php echo e(number_format($value->harga_jual,0,',','.')); ?></h6>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<div class="mb-4 mt-5">
  <?php echo e($data['produk']->links('include.pagination', ['pagination' => $data['produk']] )); ?>

</div>
<div id="filter-barang" class="modal fade right">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Filter Barang</h5>
      </div>
      <div class="modal-body">
        <form action="<?php echo e(url('main/belanja/produk/filter')); ?>" method="post">
          <?php echo e(csrf_field()); ?>

          <div class="form-group">
            <label>Kelompok Barang</label>
            <select class="select2" style="width:100%" id="kelompok" name="kelompok"></select>
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select class="select2" style="width:100%" id="kategori" name="kategori"></select>
          </div>
          <div class="form-group">
            <label>Sub Kategori</label>
            <select class="select2" style="width:100%" id="sub_kategori" name="sub_kategori"></select>
          </div>
          <button class="btn btn-primary btn-block">Filter Barang </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('add_js'); ?>
<script>
selected_kelompok = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kelompok'] !='all'  ? Session::get('filter_produk')['kelompok'] : 'all')); ?>';
selected_kategori = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kategori'] !='all' ? Session::get('filter_produk')['kategori'] : 'all')); ?>';
selected_subkategori = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['sub_kategori'] !='all' ? Session::get('filter_produk')['sub_kategori'] : 'all')); ?>';
function get_kategori(select_target, parent_id, selected){
  $.get("<?php echo e(url('api/get_kategori')); ?>/"+parent_id+'/'+selected, function (result) {
    $selectElement = $('#'+select_target);
    $selectElement.empty();
    $.each(result, function (i, value) {
      $selectElement.append('<option data-id="'+value.id+'" value="'+value.id+'" '+value.selected+' >'+value.nama_kategori+'</option>');
    });
    $selectElement.trigger('change');
  });
}

get_kategori('kelompok','0', selected_kelompok);
$('#kelompok').change(function () {
    let id = $(this).find('option:selected').attr('data-id');
    get_kategori('kategori',id, selected_kategori);
});
$('#kategori').change(function () {
    let id = $(this).find('option:selected').attr('data-id');
    get_kategori('sub_kategori',id, selected_subkategori);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main.belanja.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/main/belanja/pilih_produk/index.blade.php ENDPATH**/ ?>