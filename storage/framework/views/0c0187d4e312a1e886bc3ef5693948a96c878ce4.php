<?php
    $app='sinjam';
    $page='Pengajuan Pinjaman';
    $subpage='Pengajuan Pinjaman';
?>

<?php $__env->startSection('title'); ?>
    Pengajuan Pinjaman |
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <style>
        .nav-pills .nav-link.active,
        .nav-pills .nav-link.active:hover,
        .nav-pills .show>.nav-link {
            color: #fff;
            background-color: #104f76;
        }
        .nav-pills>li>a, .nav-tabs>li>a {
            color: #f8f8fc;
            font-weight: 500;
        }
        .nav-pills .nav-link:hover {
            background-color: transparent;
            color: #004d7a;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="<?php echo e(asset('assets/images/icon-page/save-money.png')); ?>" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Pengajuan Pinjaman</h4>
                        <p class="text-muted m-0">Menampilkan data pengajuan pinjaman anggota yang sudah diinput oleh petugas atau anggota</p>
                    </div>
                </div>
            </div>
            <form action="" method="get" id="form_search">
                <div class="row">
                    <div class="col-md-1">
                        <select class="select2" name="tahun" id="tahun" style="width:100%" onchange="document.getElementById('form_search').submit();">
                            <option value="all" <?php echo e($tahun === 'all' ? 'selected' : ''); ?>>Semua</option>
                            <?php for($i = intval(date('Y'));$i >= 2018; $i--): ?>
                                <option value="<?php echo e($i); ?>" <?php echo e($tahun == $i ? 'selected' : ''); ?>><?php echo e($i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="select2" name="bulan" id="bulan" style="width:100%" onchange="document.getElementById('form_search').submit();">
                            <option value="all" <?php echo e($bulan === 'all' ? 'selected' : ''); ?>>Semua Bulan</option>
                            <?php for($i = 0; $i < 12; $i++): ?>
                                <option value="<?php echo e($i); ?>" <?php echo e($bulan === (string)$i ? 'selected' : ''); ?>><?php echo e(list_bulan()[$i]); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
                            <option value="#282828" data-id="all">Semua Status</option>
                            <?php $__currentLoopData = $data['status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value->color); ?>" <?php echo e(($status == $value->id ? 'selected' : '')); ?> data-id="<?php echo e($value->id); ?>" ><?php echo e($value->status); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="hidden" name="jenis" value="<?php echo e($jenis); ?>">
                            <input type="hidden" name="status" id="status_id" value="<?php echo e($status); ?>">
                            <input type="text" class="form-control" value="<?php echo e($search); ?>" name="search" placeholder="Cari Data Pinjaman">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="dropdown">
                            <button class="btn btn-primary btn-block dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Formulir Pinjaman</button>
                            <div class="dropdown-menu dropdown-menu-right" style="width:220px;border-radius:0px">
                                <?php $__currentLoopData = $data['jenis']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a class="dropdown-item" style="text-align:right" href="<?php echo e(url('pinjaman/pengajuan/form?type='.$value->id)); ?>"><?php echo e($value->jenis_transaksi); ?></a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-success btn-block dropdown-toggle" type="button" onclick="export_excel()">Export</button>
                    </div>
                </div>
            </form>
        </div>
        <div style="margin:-7px -24px;padding:15px 25px;background:#7f8fa6">
            <ul class="nav nav-pills" role="tablist">
                <?php $__currentLoopData = $data['jenis']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(($jenis == $value->id ? 'active' : '')); ?> " href="<?php echo e(url('pinjaman/pengajuan?jenis='.$value->id)); ?>"><?php echo e($value->jenis_transaksi); ?></a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php if(count($data['pinjaman'])==0): ?>
            <div style="width:100%;text-align:center">
                <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-5" style="width:200px">
                <h4 class="mt-3">Data Pengajuan Pinjaman tidak Ditemukan</h4>
            </div>
        <?php else: ?>
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom">
                    <thead>
                    <tr>
                        <th class="center">Tanggal</th>
                        <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
                        <th class="center" width="200px">Jenis<br>Pinjaman</th>
                        <th style="text-align:right">Jumlah<br>Pinjaman</th>
                        <th style="text-align:right">Total<br>Angsuran</th>
                        <th style="text-align:right;white-space:nowrap">Sisa Pinjaman<hr class="line-xs">Sisa Tenor</th>
                        <th style="text-align:right;white-space:nowrap">Status<hr class="line-xs">Angsuran Ke</th>
                        
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $data['pinjaman']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="center" style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')); ?></td>
                            <td>
                                <div class="media">
                                    <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                                        <img src="<?php echo e((!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )); ?>" alt="" class="rounded-circle">
                                    </div>
                                    <div class="media-body align-self-center">
                                        <p class="text-muted mb-0">No. <?php echo e($value->fid_anggota); ?></p>
                                        <h5 class="text-truncate font-size-15"><a href="<?php echo e(url('anggota/detail?id='.$value->id)); ?>" class="text-dark"><?php echo e($value->nama_lengkap); ?></a></h5>
                                    </div>
                                </div>
                            </td>
                            <td class="center"><?php echo e($value->jenis_transaksi); ?></td>
                            <td style="text-align:right;white-space:nowrap">Rp <?php echo e(format_number(str_replace('-','',$value->nominal))); ?></td>
                            <td style="text-align:right;white-space:nowrap">Rp <?php echo e(format_number(str_replace('-','',$value->total_angsuran))); ?></td>
                            <td style="text-align:right;white-space:nowrap">
                                <h6>Rp <?php echo e(format_number(str_replace('-','',$value->sisa_pinjaman))); ?></h6>
                                <?php echo e($value->sisa_tenor); ?> dari <?php echo e($value->tenor); ?>

                            </td>
                            <td style="text-align:right;white-space:nowrap">
                                <h6><?php echo e($value->status); ?></h6>
                                <?php echo e($value->tenor-$value->sisa_tenor); ?> dari <?php echo e($value->tenor); ?>

                            </td>
                            
                            <td style="width:1px;white-space:nowrap">
                                <div class="text-center">
                                    <a href="<?php echo e(url('pinjaman/pengajuan/detail?id='.$value->id)); ?>" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:20px">
                <?php echo e($data['pinjaman']->links('include.pagination', ['pagination' => $data['pinjaman']] )); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>
        function formatStatus(status) {
            var $status = $(
                '<span style="display:flex;align-items:center;"><div class="indikator-status mr-2" style="background:'+status.id+'"></div>'+status.text+'</span>'
            );
            return $status;
        };

        $(".select2-status").select2({
            templateResult: formatStatus
        });

        function pilih_status(){
            let id = $('#status_color').find('option:selected').attr('data-id');
            $('#status_id').val(id);
            $('#status_form').submit();
        }

        let export_excel = () => {
            let data = $('#form_search').serialize();
            window.open("<?php echo e(url('pinjaman/pengajuan/export')); ?>?" + data, '_blank');
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/pinjaman/pengajuan/index.blade.php ENDPATH**/ ?>