<?php
    $app='laporan';
    $page='Neraca';
    $subpage='Neraca';
?>



<?php $__env->startSection('title'); ?>
    Neraca |
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="<?php echo e(asset('assets/images/icon-page/organization-chart.png')); ?>" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Neraca</h4>
                    <p class="text-muted m-0">Menampilkan laporan buku besar</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" class="form-control monthpicker" id="bulan" name="bulan" value="<?php echo e(date('m-Y')); ?>" onchange="search()">
                    </div>
                    <div class="col-md-1 text-center d-flex flex-row justify-content-center align-items-center">
                        s/d
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control monthpicker" id="bulan_akhir" name="bulan_akhir" value="<?php echo e(date('m-Y')); ?>" onchange="search()">
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
        let _token = '<?php echo e(csrf_token()); ?>', data = [];
        let $table = $('#table');

        let search = () => {
            let bulan = $('#bulan').val();
            let bulan_akhir = $('#bulan_akhir').val();
            $table.html('<h1>Loading ...</h1>');
            $.post("<?php echo e(url('keuangan/neraca/search')); ?>", {_token, bulan, bulan_akhir}, (result) => {
                $table.html(result);
            }).fail((xhr) => {
                $table.html(xhr.responseText);
            })
        }

        let discard = () => {
            $info.html('');
            search();
        }

        let export_excel = () => {
            let bulan = $('#bulan').val();
            let bulan_akhir = $('#bulan_akhir').val();
            window.open("<?php echo e(url('keuangan/neraca/export')); ?>?bulan=" + bulan + '&bulan_akhir=' + bulan_akhir, '_blank');
        }

        search();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/keuangan/neraca/index.blade.php ENDPATH**/ ?>