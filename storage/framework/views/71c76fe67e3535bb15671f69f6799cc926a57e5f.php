<?php
    $app='laporan';
    $page='Bagan Akun';
    $subpage='Bagan Akun';
?>



<?php $__env->startSection('title'); ?>
    Bagan Akun |
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="<?php echo e(asset('assets/images/icon-page/organization-chart.png')); ?>" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Data Akun</h4>
                    <p class="text-muted m-0">Menampilkan bagan akun keuangan yang digunakan dalam proses transkasi di koperasi</p>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <button type="button" onclick="info()" class="btn btn-primary btn-block">Tambah Akun</button>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="search_akun" placeholder="Cari Kode Akun">
                            </div>
                        </div>
                        <hr>
                        <div id="tree"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-5" id="info">
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        let to, _token = '<?php echo e(csrf_token()); ?>', data = [];
        let $info = $('#info'), $tree = $('#tree');
        $tree.jstree({
            "core" : {
                "themes" : {
                    "responsive": true
                },
                "check_callback" : true,
                'data': data
            },
            "types" : {
                "default" : {
                    "icon" : "fa fa-folder m--font-brand"
                },
                "file" : {
                    "icon" : "fa fa-file  m--font-brand"
                }
            },
            "plugins" : [ "search", "state", "types", "wholerow" ],
            "search" : { "show_only_matches" : true }
        }).on("select_node.jstree", (e, data) => {
            info(data.node.original.id);
        });

        let search = () => {
            $.post("<?php echo e(url('keuangan/akun/search')); ?>", {_token}, (result) => {
                $tree.jstree(true).settings.core.data = result;
                $tree.jstree(true).refresh();
            }).fail((xhr) => {
                $tree.html(xhr.responseText);
            })
        }

        let info = (id = '', parent_kode = '') => {
            $.get("<?php echo e(url('keuangan/akun')); ?>/" + (id === '' ? 'create' : (id + '/edit')) + '?parent_kode=' + parent_kode, (result) => {
                $info.html(result);
            }).fail((xhr) => {
                console.log(xhr.responseText);
            });
        }

        let delete_data = (id) => {
            Swal.fire({
                title: 'Hapus Data Akun ?',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                denyButtonText: 'Batal',
                confirmButtonColor: '#F46A6A',
                denyButtonColor: '#bdbdbd'
            }).then((result) => {
                if (result.value) {
                    $.post("<?php echo e(url('keuangan/akun')); ?>/" + id, {_method: 'delete', _token}, () => {
                        discard();
                    }).fail((xhr) => {
                        $info.html(xhr.responseText);
                    });
                }
            });
        }

        let discard = () => {
            $info.html('');
            search();
        }

        search();

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/keuangan/akun/index.blade.php ENDPATH**/ ?>