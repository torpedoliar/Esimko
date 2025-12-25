<?php
    $app='laporan';
    $page='Buku Besar';
    $subpage='Buku Besar';
?>



<?php $__env->startSection('title'); ?>
    Buku Besar |
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="<?php echo e(asset('assets/images/icon-page/organization-chart.png')); ?>" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Buku Besar</h4>
                    <p class="text-muted m-0">Menampilkan laporan buku besar</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" class="form-control monthpicker" id="bulan" name="bulan" value="<?php echo e(date('m-Y')); ?>" onchange="search_data()">
                    </div>
                    <div class="col-md-1 text-center d-flex flex-row justify-content-center align-items-center">
                        s/d
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control monthpicker" id="bulan_akhir" name="bulan_akhir" value="<?php echo e(date('m-Y')); ?>" onchange="search_data()">
                    </div>
                    <div class="col-md-4">
                        <select type="text" class="form-control select2" name="akun_id" id="akun_id" onchange="search_data()">
                            <?php $__currentLoopData = $akun; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($item->id); ?>"><?php echo e($item->kode_tampil . ' - ' . $item->nama); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success text-nowrap" type="button" onclick="export_excel()">Export Excel</button>
                    </div>
                </div>
                <hr>
                <div id="table"></div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        let _token = '<?php echo e(csrf_token()); ?>', data = [], selected_page = 1;
        let $table = $('#table');

        let search_data = (page = 1) => {
            if (page.toString() === '+1') selected_page++;
            else if (page.toString() === '-1') selected_page--;
            else selected_page = page;

            let bulan = $('#bulan').val();
            let bulan_akhir = $('#bulan_akhir').val();
            let akun_id = $('#akun_id').find('option:selected').val();
            $table.html('<h1>Loading ...</h1>');
            $.post("<?php echo e(url('keuangan/buku_besar/search')); ?>", {_token, bulan, bulan_akhir, akun_id, page: selected_page}, (result) => {
                $table.html(result);
            }).fail((xhr) => {
                $table.html(xhr.responseText);
            })
        }

        let discard = () => {
            $info.html('');
            search_data();
        }

        let export_excel = () => {
            let bulan = $('#bulan').val();
            let bulan_akhir = $('#bulan_akhir').val();
            let akun_id = $('#akun_id').find('option:selected').val();
            window.open("<?php echo e(url('keuangan/buku_besar/export')); ?>?bulan=" + bulan + '&bulan_akhir=' + bulan_akhir + '&akun_id=' + akun_id, '_blank');
        }

        search_data();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/keuangan/buku_besar/index.blade.php ENDPATH**/ ?>