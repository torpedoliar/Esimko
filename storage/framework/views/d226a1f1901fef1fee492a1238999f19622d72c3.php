<?php
    $app='manajemen_barang';
    $page='Data Barang';
    $subpage='Data Barang';
?>

<?php $__env->startSection('title'); ?>
    Data Barang |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="<?php echo e(asset('assets/images/icon-page/boxes.png')); ?>" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Data Barang</h4>
                    <p class="text-muted m-0">Formulir pengisian data barang atau produk yang dijual ditoko secara online atau offline</p>
                </div>
            </div>
        </div>
        <form action="<?php echo e(url('manajemen_stok/barang/proses')); ?>" method="post" enctype="multipart/form-data" id="form_barang">
            <?php echo e(csrf_field()); ?>

            <div class="card">
                <div class="card-header">
                    <h5><?php echo e(($action=='add' ? 'Tambah' : 'Edit')); ?> Barang</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <div class="produk-wrapper" style="height:305px;width:305px;padding:5px" data-tippy-placement="bottom">
                                <img src="<?php echo e((!empty($data['produk']->foto) ? asset('storage/'.$data['produk']->foto) : asset('assets/images/produk-default.jpg'))); ?>" alt="" />
                                <div class="upload-button" onclick="changeImage('produk')"></div>
                                <input class="file-upload" type="file" name="foto" accept="image/*"/>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kode</label>
                                        <input type="text" class="form-control" name="kode" value="<?php echo e((!empty($data['produk']) ? $data['produk']->kode : '')); ?>"  autocomplete="off" required >
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Nama Produk</label>
                                        <input type="text" class="form-control" name="nama_produk" value="<?php echo e((!empty($data['produk']) ? $data['produk']->nama_produk : '')); ?>"  autocomplete="off" required >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kelompok Barang</label>
                                        <select class="select2" style="width:100%" id="kelompok" name="kelompok"></select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kategori</label>
                                        <select class="select2" style="width:100%" id="kategori" name="kategori"></select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Sub Kategori</label>
                                        <select class="select2" style="width:100%" id="sub_kategori" name="sub_kategori"></select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Stok Awal</label>
                                        <input type="text" class="form-control center" name="stok_awal" value="<?php echo e((!empty($data['produk']) ? $data['produk']->stok_awal : 0)); ?>" required >
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Stok Minimal</label>
                                        <input type="text" class="form-control center" name="stok_minimal" value="<?php echo e((!empty($data['produk']) ? $data['produk']->stok_minimal : 0)); ?>" required >
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Satuan</label>
                                        <select class="select2" style="width:100%" name="satuan">
                                            <?php $__currentLoopData = $data['satuan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($value->id); ?>" <?php echo e((!empty($data['produk']) && $data['produk']->fid_satuan == $value->id ? 'selected' : '')); ?> ><?php echo e($value->satuan); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Harga Beli</label>
                                        <input type="text" class="form-control autonumeric" onkeyup="calc_harga('margin')" id="harga_beli" value="<?php echo e((!empty($data['produk']) ? $data['produk']->harga_beli : 0 )); ?>" data-a-dec="," data-a-sep="." name="harga_beli"  required >
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Margin Penjualan</label>
                                        <div style="display:flex">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                                <input type="text" class="form-control autonumeric" name="nominal_margin" id="nominal_margin" onkeyup="calc_harga('nominal')" data-a-dec="," data-a-sep="." style="border-radius:0px" value="<?php echo e((!empty($data['produk']) ? $data['produk']->margin_nominal : 0 )); ?>"  >
                                            </div>
                                            <div class="input-group" style="width:150px" >
                                                <input type="text"class="form-control" style="border-radius:0px;margin-left:-1px;text-align:center" onkeyup="calc_harga('margin')" id="margin" name="margin" value="<?php echo e((!empty($data['produk']) ? $data['produk']->margin : 0 )); ?>"  required >
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Harga Jual</label>
                                        <input type="text" class="form-control autonumeric" id="harga_jual" data-a-dec="," data-a-sep="." name="harga_jual" value="<?php echo e((!empty($data['produk']) ? $data['produk']->harga_jual : 0 )); ?>" readonly >
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="select2" style="width:100%" name="is_aktif">
                                            <option value="1" <?php echo e((!empty($data['produk']) && $data['produk']->is_aktif == 1 ? 'selected' : '')); ?> >Aktif</option>
                                            <option value="0" <?php echo e((!empty($data['produk']) && $data['produk']->is_aktif == 0 ? 'selected' : '')); ?> >Tidak Aktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Expired</label>
                                        <input type="text" class="form-control datepicker" id="expired" name="expired" value="<?php echo e((!empty($data['produk']) ? format_date($data['produk']->expired) : 0 )); ?>" readonly />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea class="form-control" name="deskripsi" style="height:117px" ><?php echo e((!empty($data['produk']) ? $data['produk']->deskripsi : '')); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <input type="hidden" name="halaman" value="<?php echo e($request->page); ?>">
                    <input type="hidden" name="action" value="<?php echo e($action); ?>">
                    <input type="hidden" name="id" value="<?php echo e($id); ?>">
                    <div class="pull-right">
                        <a class="btn btn-secondary" href="<?php echo e(url('manajemen_stok/barang')); ?>" >Kembali</a>
                        <button class="btn btn-primary" type="submit"><?php echo e(($action=='add' ? 'Tambah' : 'Simpan')); ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script src="<?php echo e(asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/pages/form-advanced.init.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/accounting.js')); ?>"></script>
    <script>
        $('#form_barang').submit((e) => {
            $('#form_barang').attr('disabled', 'disabled');
        });

        let selected_kelompok = '<?php echo e((!empty($data['produk']) ? $data['produk']->kelompok : 'all' )); ?>';
        let selected_kategori = '<?php echo e((!empty($data['produk']) ? $data['produk']->kategori : 'all' )); ?>';
        let selected_subkategori = '<?php echo e((!empty($data['produk']) ? $data['produk']->subkategori : 'all' )); ?>';

        function get_kategori(select_target, parent_id, selected){
            $.get("<?php echo e(url('api/get_kategori')); ?>/"+parent_id, function (result) {
                $selectElement = $('#'+select_target);
                $selectElement.empty();
                $.each(result, function (i, value) {
                    $selectElement.append('<option data-id="'+value.id+'" value="'+value.id+'">'+value.nama_kategori+'</option>');
                });
                if(selected !== ''){
                    $selectElement.val(selected);
                    $selectElement.select2();
                }
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


        // calc_harga();
        function calc_harga(jenis='all'){
            harga_beli=$('#harga_beli').val();
            harga_beli = harga_beli.split('.').join('');

            if(jenis == 'nominal'){
                nominal_margin=$('#nominal_margin').val();
                nominal_margin = nominal_margin.split('.').join('');
                margin = (nominal_margin/harga_beli)*100,2;
                $('#margin').val(accounting.formatNumber(margin,0,'.',','));
            }
            else{
                margin=$('#margin').val();
                nominal_margin=margin*harga_beli/100,2;
                $('#nominal_margin').val(accounting.formatNumber(nominal_margin,0,'.',','));
            }
            harga_jual=parseInt(harga_beli)+parseInt(nominal_margin);
            $('#harga_jual').val(accounting.formatNumber(harga_jual,0,'.',','));
        }

        function changeImage(target) {
            var readURL = function(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('.'+target+'-wrapper img').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };

            $(".file-upload").on('change', function(){
                readURL(this);
            });
            $(".file-upload").click();
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/manajemen_stok/barang/form.blade.php ENDPATH**/ ?>