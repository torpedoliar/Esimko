<?php
    $app='manajemen_barang';
    $page='Retur Barang';
    $subpage='Retur Barang';
?>

<?php $__env->startSection('title'); ?>
    Retur Barang |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')); ?>" rel="stylesheet" />
    <style>
        .list-produk{
            padding-bottom:10px;
            border-bottom: 1px solid #f2f2f2;
            margin-top:10px;
            cursor: pointer;
        }
        .image-square{
            background-color:#ececec;
            position: relative; /* If you want text inside of it */
            background-position: 50% 50%;
            background-repeat: no-repeat;
            background-size: cover;
        }
        /* .input-group>.input-group-append:not(:last-child)>.input-group-text{
          background: #fff !important
        }
        .input-group>.form-control:not(:first-child){
          border-right:0px;
        }
        .input-group>.form-control:focus {
          z-index: 0;
        } */
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="<?php echo e(asset('assets/images/icon-page/return-box.png')); ?>" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Retur Barang</h4>
                    <p class="text-muted m-0">Formulir pengisian data retur barang ke supplier yang sudah dibeli</p>
                </div>
            </div>
        </div>
        <form action="<?php echo e(url('manajemen_stok/return/proses')); ?>" method="post" enctype="multipart/form-data">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="fid_pembelian" value="<?php echo e($fid_pembelian ?? ''); ?>">
            <input type="hidden" name="action" value="<?php echo e($action); ?>">
            <input type="hidden" name="id" value="<?php echo e($id); ?>">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo e(($action=='add' ? 'Tambah' : 'Edit')); ?> Retur Pembelian</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>No. Pembelian</label>
                                <input type="text" name="no_pembelian" id="no_pembelian" value="<?php echo e($no_pembelian); ?>" autocomplete="off" class="form-control" onchange="cari_pembelian()" >
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="text" name="tanggal" value="<?php echo e((!empty($data['retur']) ? \App\Helpers\GlobalHelper::dateFormat($data['retur']->tanggal,'d-m-Y') : date('d-m-Y'))); ?>" autocomplete="off" class="datepicker form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>No. Retur Pembelian</label>
                                <input type="text" name="no_retur" value="<?php echo e((!empty($data['retur']) ? $data['retur']->no_retur : '')); ?>" autocomplete="off" class="form-control" readonly >
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Keterangan</label>
                                <input type="text" name="keterangan" value="<?php echo e((!empty($data['retur']) ? $data['retur']->keterangan : '')); ?>" autocomplete="off" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Supplier</label>
                                <select name="supplier" id="supplier" class="form-control select2">
                                    <?php $__currentLoopData = $data['supplier']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value->id); ?>" <?php echo e(($pembelian->fid_supplier ?? $fid_supplier) == $value->id ? 'selected' : ''); ?>><?php echo e($value->nama_supplier); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <table class="table table-middle table-bordered table-hover">
                        <thead class="thead-light">
                        <tr>
                            <th width="50px">No</th>
                            <th>Produk</th>
                            <th class="center" style="width:170px">Metode Retur</th>
                            <th class="center" style="width:140px">Jumlah<br>Barang</th>
                            <th class="center" style="width:140px">Harga Beli <hr style="margin-top: 0.5rem;margin-bottom: 0.5rem;"> Total Harga</th>
                            <th width="150px"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>#</td>
                            <td onclick="pilih_produk('show')">
                                <div class="media">
                                    <div class="rounded mr-3 produk-wrapper" id="foto" style="height:50px;width:50px;border:1px solid #e4e4e4">
                                        <img src="<?php echo e(asset('assets/images/produk-default.jpg')); ?>" alt="" />
                                    </div>
                                    <div class="align-self-center media-body">
                                        <div id="kode" >
                                            <div style="height:15px;width:150px;background:whitesmoke"></div>
                                        </div>
                                        <div id="nama_produk">
                                            <div style="height:20px;width:250px;background:whitesmoke" class="mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <select name="metode" id="metode" class="select2" style="width:100%">
                                    <option value="Tukar Barang">Tukar Barang</option>
                                    <option value="Kembali Uang">Kembali Uang</option>
                                </select>
                            </td>
                            <td>
                                <input data-toggle="touchspin" onchange="calc_items()" id="jumlah" name="jumlah" value="1" type="text" class="center">
                                <div id="satuan" class="mt-1" style="text-align:right;color:#444444"></div>
                            </td>
                            <td>
                                <input type="text" class="form-control autonumeric" onkeyup="calc_items()" data-a-dec="," data-a-sep="." name="harga" id="harga" >
                                <div id="total_harga" class="mt-1" style="text-align:right;color:#444444"></div>
                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <input type="hidden" name="produk_id" id="produk_id">
                                <input type="hidden" name="items_id" id="items_id">
                                <button class="btn btn-primary btn-block" id="action">Tambah</button>
                                <button type="button" class="btn btn-dark btn-block" id="cancel" onclick="add_items()">Cancel</button>
                            </td>
                        </tr>
                        <?php $__currentLoopData = $data['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($key+1); ?></td>
                                <td>
                                    <div class="media">
                                        <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px;border:1px solid #e4e4e4">
                                            <img src="<?php echo e((!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                                        </div>
                                        <div class="align-self-center media-body">
                                            <span>Kode. <?php echo e($value->kode); ?></span>
                                            <h6><?php echo e($value->nama_produk); ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="center"><?php echo e($value->metode); ?></td>
                                <td class="center"><?php echo e($value->jumlah); ?><br><?php echo e($value->satuan); ?></td>
                                <td style="text-align:right">
                                    Rp <?php echo e(number_format($value->harga,0,',','.')); ?>

                                    <div class="font-weight-semibold">Rp <?php echo e(number_format($value->total,0,',','.')); ?></div>
                                </td>
                                <td style="width:1px;white-space:nowrap">
                                    <div class="text-center">
                                        <a href="javascript:;" onclick="edit_items(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                                        <a href="javascript:;" onclick="confirmDelete(<?php echo e($value->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $list_produk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>-</td>
                                <td>
                                    <div class="media">
                                        <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px;border:1px solid #e4e4e4">
                                            <img src="<?php echo e((!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                                        </div>
                                        <div class="align-self-center media-body">
                                            <span>Kode. <?php echo e($value->kode); ?></span>
                                            <h6><?php echo e($value->nama_produk); ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select name="metode" id="metode_<?php echo e($key); ?>" class="select2" style="width:100%">
                                        <option value="Tukar Barang">Tukar Barang</option>
                                        <option value="Kembali Uang">Kembali Uang</option>
                                    </select>
                                </td>
                                <td>
                                    <input data-toggle="touchspin" onchange="calc_items2(<?php echo e($value->id); ?>)" id="jumlah_<?php echo e($value->id); ?>" name="jumlah" value="0" type="text" class="center">
                                    <div id="satuan" class="mt-1" style="text-align:right;color:#444444"></div>
                                </td>
                                <td>
                                    <input type="text" class="form-control autonumeric" value="<?php echo e($value->harga_beli); ?>" onkeyup="calc_items2(<?php echo e($value->id); ?>)" data-a-dec="," data-a-sep="." name="harga" id="harga_<?php echo e($value->id); ?>" >
                                    <div id="total_harga_<?php echo e($value->id); ?>" class="mt-1" style="text-align:right;color:#444444"></div>
                                </td>
                                <td style="width:1px;white-space:nowrap">
                                    <input type="hidden" name="produk_id" id="produk_id_<?php echo e($value->id); ?>" value="<?php echo e($value->id); ?>">
                                    <input type="hidden" id="harga_satuan_<?php echo e($value->id); ?>" value="<?php echo e($value->harga_beli); ?>">
                                    <button class="btn btn-primary btn-block" id="action">Tambah</button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <th colspan="4">Total</th>
                            <th class="text-right">Rp. <?php echo e(format_number($data['items']->sum('total'))); ?></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <input type="hidden" name="id" value="<?php echo e($id); ?>">
                    <div class="pull-right">
                        <a class="btn btn-secondary" href="<?php echo e(url('manajemen_stok/return')); ?>" >Kembali</a>
                        <?php if($action=='edit'): ?>
                            <button class="btn btn-primary" type="submit" name="action" value="edit_transaksi">Simpan</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php $__currentLoopData = $data['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <form action="<?php echo e(url('manajemen_stok/return/proses')); ?>" method="post" id="hapus<?php echo e($value->id); ?>">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="id" value="<?php echo e($id); ?>">
            <input type="hidden" name="produk_id" value="<?php echo e($value->fid_produk); ?>">
            <input type="hidden" name="items_id" value="<?php echo e($value->id); ?>">
            <input type="hidden" name="action" value="delete_items">
        </form>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <div id="modal-produk" class="modal fade right">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Pilih Produk</h5>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="" id="search" name="search" placeholder="Cari Produk">
                        <div class="input-group-append">
                            <button class="btn btn-dark" id="btn-search" onclick="search_produk()">Search</button>
                        </div>
                    </div>
                    <div id="loading"><img src="<?php echo e(asset('assets/images/loading.gif')); ?>" style="width:100px"></div>
                    <div id="list-produk" ></div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script src="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/pages/form-advanced.init.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/accounting.js')); ?>"></script>
    <script>
        $(function () {
            $('#cancel').trigger('click');
        });

        function search_produk(){
            var search = $('#search').val();
            if(search !== ''){ search = search }
            else{ search = 'all'}
            var supplier = $('#supplier').val();
            if(supplier !== ''){ supplier = '/'+supplier }
            else{ supplier = '/all'}
            $('#loading').show();
            $('#list-produk').hide();
            $.get("<?php echo e(url('api/get_produk/')); ?>"+supplier+"/"+search,function (result) {
                $('#list-produk').html('');
                $.each(result,function(i,value){
                    $('#list-produk').append('<div class="list-produk" onclick="pilih_produk('+value.id+','+value.harga_beli+')">'+
                        '<div class="media">'+
                        '<div class="image-square avatar-md mr-2" style="max-width:none;background-image:url('+value.foto+')"></div>'+
                        '<div class="media-body align-self-center" >'+
                        '<p class="text-muted mb-0">Kode. '+value.kode+'</p>'+
                        '<h5 class="font-size-16">'+value.nama_produk+'</h5>'+
                        '</div>'+
                        '</div>'+
                        '</div>');
                });
                $('#loading').hide();
                $('#list-produk').show();
            });
        };

        function pilih_produk(id,harga_beli){
            if(id=='show'){
                search_produk();
                $('#modal-produk').modal('show');
            }
            else{
                $.get("<?php echo e(url('api/find_produk')); ?>/"+id,function(result){
                    $('#foto').html('<img src="'+result.foto+'" alt="" >');
                    $('#kode').html('<span>Kode. '+result.kode+'</span>');
                    $('#nama_produk').html('<h6>'+result.nama_produk+'</h6>');
                    $('#harga').val(accounting.formatNumber(harga_beli,0,'.',','));
                    $('#jumlah').val(1);
                    $('#satuan').html('satuan : '+result.satuan);
                    $('#produk_id').val(id);
                    $('#modal-produk').modal('hide');
                    calc_items();
                });
            }
        }

        function calc_items(){
            jumlah=$('#jumlah').val();

            harga=$('#harga').val();
            harga=harga.split('.').join('');

            total_harga=jumlah*harga;
            $('#total_harga').html('Rp '+accounting.formatNumber(total_harga,0,'.',','));
        }

        function calc_items2(id){
            jumlah=$('#jumlah_' + id).val();

            harga=$('#harga_satuan_' + id).val();
            harga=harga.split('.').join('');

            total_harga=jumlah*harga;
            $('#total_harga_' + id).html('Rp '+accounting.formatNumber(total_harga,0,'.',','));
        }

        function add_items(){
            $('#produk_id').val('');
            $('#kode').html('<div style="height:15px;width:150px;background:whitesmoke"></div>');
            $('#nama_produk').html('<div style="height:20px;width:250px;background:whitesmoke" class="mt-2"></div>');
            $('#jumlah').val('');
            $('#harga').val('');
            $('#items_id').val(0);
            $('#action').html('Tambah');
            $('#cancel').hide();
        }

        function edit_items(id){
            $.get("<?php echo e(url('api/find_items_return_pembelian')); ?>/"+id,function(result){
                $('#produk_id').val(result.fid_produk);
                $('#items_id').val(id);
                $('#kode').html('<span>'+result.kode+'</span>');
                $('#nama_produk').html('<h6>'+result.nama_produk+'</h6>');
                $('#jumlah').val(result.jumlah);
                $('#harga').val(accounting.formatNumber(result.harga,0,'.',','));
                $('#metode').val(result.metode);
                $('#metode').select2();
                $('#satuan').html('satuan : '+result.satuan);
                $('#action').html('Edit');
                $('#cancel').show();
            });
        }

        function cari_pembelian()
        {
            let no = $('#no_pembelian').val();
            <?php if($id === ''): ?>
                window.location.href = "<?php echo e(url('manajemen_stok/return/form')); ?>?no_pembelian=" + no;
            <?php else: ?>
                window.location.href = "<?php echo e(url('manajemen_stok/return/form')); ?>?id=<?php echo e($id); ?>&no_pembelian=" + no;
            <?php endif; ?>
        }

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/manajemen_stok/return/form.blade.php ENDPATH**/ ?>