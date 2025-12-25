<?php
    $app='manajemen_barang';
    $page='Cetak Label';
    $subpage='Label Harga';
?>

<?php $__env->startSection('title'); ?>
    Label Harga |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/tag.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Cetak Label Harga</h4>
                        <p class="text-muted m-0">Halaman untuk mencetak label harga dari barang yang teserdia di toko</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" value="<?php echo e($search); ?>" placeholder="Cari Data Barang">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary btn-block" data-toggle="modal" data-target='#filter-barang' >Tambahkan Barang</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-primary btn-block" href="<?php echo e(url('manajemen_stok/cetak/label_harga?mode=cetak&search=' . $search)); ?>" target="_blank" >Cetak Label</a>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-warning btn-block" href="<?php echo e(url('manajemen_stok/cetak/label_harga?mode=kosongi')); ?>" >Kosongi</a>
                </div>
            </div>
        </div>
        <?php if(count($data['produk'])==0): ?>
            <div style="width:100%;text-align:center">
                <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
                <h4 class="mt-2">DATA BARANG TIDAK DITEMUKAN</h4>
            </div>
        <?php else: ?>
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom" id="datatable">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Barang<hr class="line-xs">Nama Barang</th>
                        <th class="center">Kategori Produk</th>
                        <th style="text-align:right">Harga Jual</th>
                        <th class="center" width="100px">Jumlah<br>Label</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $data['produk']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($key+1); ?></td>
                            <td>
                                <div class="media">
                                    <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                                        <img src="<?php echo e((!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                                    </div>
                                    <div class="align-self-center media-body">
                                        <span>Kode. <?php echo e($value->kode); ?></span>
                                        <h6><?php echo e($value->nama_produk); ?></h6>
                                    </div>
                                </div>
                            </td>
                            <td class="center">
                                <div style="font-weight:600"><?php echo e($value->kelompok); ?></div>
                                <div><?php echo e($value->kategori); ?></div>
                                <div class="text-muted"><?php echo e($value->sub_kategori); ?></div>
                            </td>
                            <td style="text-align:right;white-space:nowrap">Rp <?php echo e(number_format($value->harga_jual,0,',','.')); ?></td>
                            <td><input type="text" class="form-control center" value="<?php echo e($value->jumlah); ?>" id="jumlah_<?php echo e($value->id); ?>" onchange="edit_jumlah(<?php echo e($value->id); ?>)"></td>
                            <td style="width:1px;white-space:nowrap">
                                <div class="text-center">
                                    <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                    <form action="<?php echo e(url('manajemen_stok/cetak/label_harga/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
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
            </div>
        <?php endif; ?>
    </div>
    <?php $__currentLoopData = $data['produk']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <div id="filter-barang" class="modal fade">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form action="<?php echo e(url('manajemen_stok/cetak/label_harga/filter')); ?>" method="post">
                    <?php echo e(csrf_field()); ?>

                    <div class="modal-header">
                        <h5>Pilih Barang</h5>
                    </div>
                    <div class="modal-body">
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
                        <div class="form-group">
                            <label>Search</label>
                            <input type="text" class="form-control" id="search" name="search" >
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>

        function edit_jumlah(id){
            jumlah=$('#jumlah_'+id).val();
            $.ajax({
                url:'<?php echo e(url('manajemen_stok/cetak/label_harga/proses')); ?>',
                method:'POST',
                data:{
                    _token: "<?php echo e(csrf_token()); ?>",
                    id: id,
                    action: 'edit',
                    jumlah:jumlah,
                },
                error:function(error){
                    console.log(error)
                }
            });
        }

        table = $('#datatable').DataTable({
            "ordering": false,
            "bLengthChange": false,
            "bSearchable": false,
            "filter": false

        });

        selected_kelompok = '<?php echo e((!empty(Session::get('filter_label_harga')) && Session::get('filter_label_harga')['kelompok'] !='all'  ? Session::get('filter_label_harga')['kelompok'] : 'all')); ?>';
        selected_kategori = '<?php echo e((!empty(Session::get('filter_label_harga')) && Session::get('filter_label_harga')['kategori'] !='all' ? Session::get('filter_label_harga')['kategori'] : 'all')); ?>';
        selected_subkategori = '<?php echo e((!empty(Session::get('filter_label_harga')) && Session::get('filter_label_harga')['sub_kategori'] !='all' ? Session::get('filter_label_harga')['sub_kategori'] : 'all')); ?>';
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/manajemen_stok/cetak/label_harga/index.blade.php ENDPATH**/ ?>