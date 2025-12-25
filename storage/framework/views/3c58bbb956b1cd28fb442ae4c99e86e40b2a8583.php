<?php
    $app='pos';
    $page='Laporan';
    $subpage='Laporan Produk';
?>

<?php $__env->startSection('title'); ?>
    Laporan Stock |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/market.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Data Laporan Stock</h4>
                        <p class="text-muted m-0">Menampilkan laporan stock</p>
                    </div>
                </div>
            </div>

            <form id="form_search">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <div class="col-md-2">
                        <select class="select2" style="width:100%" id="kelompok" name="kelompok"></select>
                    </div>
                    <div class="col-md-2">
                        <select class="select2" style="width:100%" id="kategori" name="kategori"></select>
                    </div>
                    <div class="col-md-2">
                        <select class="select2" style="width:100%" id="sub_kategori" name="sub_kategori"></select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="tanggal_awal" placeholder="Tanggal Awal" />
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="tanggal_akhir" placeholder="Tanggal Akhir" />
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search" placeholder="Pencarian Barang" />
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-dark btn-block" type="submit" id="button_search">Search</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-success btn-block" type="button" onclick="export_Excel()">Export</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-primary btn-block" type="button" onclick="cetak()">Cetak</button>
                    </div>
                </div>
            </form>

            <div id="table"></div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>
        let $form_search = $('#form_search'),
            $table = $('#table'),
            selected_page = 1;

        $form_search.submit((e) => {
            e.preventDefault();
            search_data();
        });

        let search_data = (page = 1) => {
            if (page.toString() === '+1') selected_page++;
            else if (page.toString() === '-1') selected_page--;
            else selected_page = page;

            let data = get_form_data($form_search);
            data.paginate = 10;
            data.page = page;
            $('#button_search').html('Loading ... ');
            $.post("<?php echo e(url('pos/laporan_stock/search')); ?>", data, (result) => {
                $table.html(result);
                $('#button_search').html('Search');
            }).fail((xhr) => {
                $table.html(xhr.responseText);
            });
        }
        search_data();

        let export_Excel = () => {
            let data = $form_search.serialize();
            window.open("<?php echo e(url('pos/laporan_stock/excel')); ?>?" + data, '_blank');
        }

        let cetak = () => {
            let data = $form_search.serialize();
            window.open("<?php echo e(url('pos/laporan_stock/cetak')); ?>?" + data, '_blank');
        }


        selected_kelompok = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kelompok'] !='all'  ? Session::get('filter_produk')['kelompok'] : 'all')); ?>';
        selected_kategori = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kategori'] !='all' ? Session::get('filter_produk')['kategori'] : 'all')); ?>';
        selected_subkategori = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['sub_kategori'] !='all' ? Session::get('filter_produk')['sub_kategori'] : 'all')); ?>';
        selected_is_aktif = '<?php echo e((!empty(Session::get('filter_produk')) && Session::get('filter_produk')['is_aktif'] != 'all' ? Session::get('filter_produk')['is_aktif'] : 'all')); ?>';
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pos/laporan/produk/index.blade.php ENDPATH**/ ?>