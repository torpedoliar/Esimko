<?php
    $app='sinjam';
    $page='Monitoring Anggota';
    $subpage='Detail Saldo Simpanan';
?>

<?php $__env->startSection('title'); ?>
    Monitoring Detail Saldo Simpanan |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/wallet.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Monitoring Detail Saldo Simpanan</h4>
                        <p class="text-muted m-0">Menampilkan detail data saldo simpanan anggota</p>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin:0 -24px;padding:15px 25px;background:#7f8fa6">
            <ul class="nav nav-pills" role="tablist">
                <?php $__currentLoopData = $list_jenis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(($jenis == $key ? 'active' : '')); ?> " href="<?php echo e(url('monitoring/saldo_simpanan/detail?no_anggota=' . ($anggota->no_anggota ?? '') . '&jenis=' . $key . '&tanggal_awal=' . $tanggal_awal . '&tanggal_akhir=' . $tanggal_akhir)); ?>"><?php echo e($value); ?></a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php if(!empty($anggota)): ?>
            <div class="card" style="margin: 0 -24px;">
                <div class="card-body">
                    <div class="media">
                        <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                            <img src="<?php echo e((!empty($anggota->avatar) ? asset('storage/'.$anggota->avatar) : asset('assets/images/user-avatar-placeholder.png') )); ?>" alt="" class="rounded-circle">
                        </div>
                        <div class="media-body align-self-center">
                            <p class="text-muted mb-0">No. <?php echo e($anggota->fid_anggota); ?></p>
                            <h5 class="text-truncate font-size-15"><a href="<?php echo e(url('anggota/detail?id='.$anggota->id)); ?>" class="text-dark"><?php echo e($anggota->nama_lengkap ?? '-Kosong-'); ?></a></h5>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
            <div class="card" style="margin: 0 -24px;">
                <div class="card-body">
                    <form action="<?php echo e(url('monitoring/saldo_simpanan/detail?no_anggota=' . ($anggota->no_anggota ?? '') . '&jenis=' . $jenis . '&tanggal_awal=' . $tanggal_awal . '&tanggal_akhir=' . $tanggal_akhir)); ?>" class="row" id="form_search">
                        <input type="hidden" name="jenis" value="<?php echo e($jenis); ?>">
                        <div class="col-lg-2">
                            <input type="text" class="form-control datepicker" name="tanggal_awal" placeholder="Tanggal Awal" value="<?php echo e($tanggal_awal); ?>">
                        </div>
                        <div class="col-lg-2">
                            <input type="text" class="form-control datepicker" name="tanggal_akhir" placeholder="Tanggal Akhir" value="<?php echo e($tanggal_akhir); ?>">
                        </div>
                        <div class="col-lg-2">
                            <input type="text" class="form-control" name="no_anggota" placeholder="No. Anggota" value="<?php echo e($no_anggota); ?>">
                        </div>
                        <div class="col-lg-2">
                            <button class="btn btn-block btn-primary" type="submit">Tampilkan</button>
                        </div>
                        <div class="col-lg-2">
                            <button class="btn btn-block btn-success" type="button" onclick="export_excel()">Export</button>
                        </div>
                    </form>
                </div>
            </div>

        <div class="table-responsive mt-4 mb-4">
            <table class="table table-middle table-custom">
                <thead>
                <tr>
                    <th>Tanggal</th>
                    <?php if(empty($anggota)): ?>
                        <th>No. Anggota</th>
                        <th>Anggota</th>
                    <?php endif; ?>
                    <th style="text-align:right;width:150px">Nominal</th>
                    <th>Keterangan</th>
                    <th>Operator</th>
                    <th width="50px"></th>
                </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(format_date($value->tanggal)); ?></td>
                        <?php if(empty($anggota)): ?>
                            <td><?php echo e($value->anggota->no_anggota); ?></td>
                            <td><?php echo e($value->anggota->nama_lengkap); ?></td>
                        <?php endif; ?>
                        <td style="text-align:right">Rp <?php echo e(format_number($value->nominal)); ?></td>
                        <td><?php echo e($value->keterangan); ?></td>
                        <td><?php echo e($value->operator->nama_lengkap); ?></td>
                        <td class="py-0 align-middle text-nowrap">
                            <button class="btn btn-sm btn-secondary" type="button" onclick="edit_simpanan(<?php echo e($value); ?>)">Edit</button>
                            <button class="btn btn-sm btn-danger ml-3" type="button" onclick="confirm_hapus(<?php echo e($value->id); ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td colspan="<?php echo e(empty($anggota) ? 3 : 1); ?>">Total</td>
                    <td style="text-align:right;width:150px"><?php echo e(format_number($data->sum('nominal'))); ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal_simpanan" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5>Ubah Simpanan</h5></div>
                <div class="modal-body" id="modal_simpanan_body">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="text" name="tanggal" id="tanggal" autocomplete="off" class="datepicker form-control">
                    </div>
                    <div class="form-group">
                        <label>Jumlah Simpanan</label>
                        <input type="text" class="form-control" id="nominal_simpanan" data-a-dec="." data-a-sep="," name="nominal" />
                    </div>
                    <div class="form-group ">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" id="keterangan" autocomplete="off" class="form-control" />
                    </div>
                    <hr>
                    <button class="btn btn-primary" onclick="update_simpanan()" id="button_simpan">Simpan</button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        export_excel = () => {
            let data = $('#form_search').serialize();
            window.open("<?php echo e(url('monitoring/saldo_simpanan/export?no_anggota=' . ($anggota->no_anggota ?? '') . '&jenis=' . $jenis . '&tanggal_awal=' . $tanggal_awal . '&tanggal_akhir=' . $tanggal_akhir)); ?>&" + data, '_blank');
        }

        let selected_id = '';
        let edit_simpanan = (data) => {
            selected_id = data.id;
            $('#tanggal').val(data.tanggal);
            $('#nominal_simpanan').val(data.nominal);
            $('#keterangan').val(data.keterangan);
            $('#modal_simpanan').modal('show');
        }

        let update_simpanan = () => {
            $('#button_simpan').html('Loading ...').attr('disabled', 'disabled');
            $.post("<?php echo e(url('monitoring/saldo_simpanan/update')); ?>", {
                _token: '<?php echo e(csrf_token()); ?>',
                id: selected_id,
                tanggal: $('#tanggal').val(),
                nominal: $('#nominal_simpanan').val(),
                keterangan: $('#keterangan').val()
            }, () => {
                window.location.reload();
                $('#modal_simpanan').modal('hide');
            }).fail((xhr) => {
                $('#modal_simpanan_body').html(xhr.responseText);
            });
        }

        let confirm_hapus = (id) => {
            Swal.fire({
                title: "Hapus Simpanan ?",
                text: "Apakah anda yakin ingin menghapus simpanan ?",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Ya'
            }).then((result) => {
                if (result.value === true) proses_hapus(id);
            });
        }

        let proses_hapus = (id) => {
            Swal.fire({
                title: "Konfirmasi Terakhir?",
                text: "Apakah benar benar yakin ingin menghapus simpanan ini ?",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Proses Hapus'
            }).then((result) => {
                if (result.value === true) {
                    let bulan = $('#bulan').val();
                    $.post("<?php echo e(url('monitoring/saldo_simpanan/delete')); ?>", {
                        _token: '<?php echo e(csrf_token()); ?>', id
                    }, () => {
                        window.location.reload();
                    }).fail((xhr) => {
                        $('#modal_simpanan_body').html(xhr.responseText);
                    });
                }
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/monitoring/detail_saldo_simpanan.blade.php ENDPATH**/ ?>