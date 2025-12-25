<div class="row mt-4">
  <div class="col-md-9">
    <?php if(!empty($data['detail-transaksi'])): ?>
    <div class="card">
      <div class="card-body">
        <?php if(!empty($data['keterangan'])): ?>
        <div class="center mb-5">
          <img src="<?php echo e(asset('assets/images/'.$data['detail-transaksi']->icon)); ?>" style="width:80px">
          <h4 class="mt-3"><?php echo e($data['keterangan']->label); ?></h4>
          <p><?php echo e($data['keterangan']->keterangan); ?></p>
        </div>
        <?php else: ?>
          <div class="center mb-5">
            <img src="<?php echo e(asset('assets/images/canceled.png')); ?>" style="width:80px">
            <h4 class="mt-3">Transaksi Dibatalkan</h4>
            <p>Transaksi ini sudah dibatalkan, silahkan melakukan transaksi yang lain</p>
          </div>
        <?php endif; ?>
        <h5 class="mb-3">Informasi Transaksi</h5>
        <table class="table table-informasi">
          <tr>
            <th width="180px">No. Anggota</th>
            <th width="10px">:</th>
            <td><?php echo e($data['detail-transaksi']->no_anggota); ?></td>
          </tr>
          <tr>
            <th>Nama Lengkap</th>
            <th>:</th>
            <td><?php echo e($data['detail-transaksi']->nama_lengkap); ?></td>
          </tr>
          <tr>
            <th>Jenis Transaksi</th>
            <th>:</th>
            <td><?php echo e($data['detail-transaksi']->jenis_transaksi); ?></td>
          </tr>
          <tr>
            <th>Metode Transaksi</th>
            <th>:</th>
            <td><?php echo e($data['detail-transaksi']->metode_transaksi); ?></td>
          </tr>
          <tr>
            <th>Jumlah Simpanan</th>
            <th>:</th>
            <td>Rp <?php echo e(number_format($data['detail-transaksi']->nominal,0,',','.')); ?></td>
          </tr>
          <tr>
            <th>Keterangan</th>
            <th>:</th>
            <td><?php echo e((!empty($data['detail-transaksi']->keterangan) ? $data['detail-transaksi']->keterangan : 'Tidak ada keterangan')); ?></td>
          </tr>
        </table>
        <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
        <ul class="verti-timeline list-unstyled">
          <li class="event-list">
            <div class="event-timeline-dot">
              <i class="bx bx-right-arrow-circle"></i>
            </div>
            <h6><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($data['detail-transaksi']->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($data['detail-transaksi']->created_at,'H:i:s')); ?></h6>
            <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500"><?php echo e($data['detail-transaksi']->nama_petugas); ?></span></p>
          </li>
          <?php $__currentLoopData = \App\Helpers\GlobalHelper::get_verifikasi_transaksi($id,'transaksi'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li class="event-list">
            <div class="event-timeline-dot">
              <i class="bx bx-right-arrow-circle"></i>
            </div>
            <h6><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($value->created_at)); ?>, <?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')); ?></h6>
            <p class="text-muted"><?php echo e($value->caption); ?> <span style="font-weight:500"><?php echo e($value->nama_lengkap); ?></span></p>
          </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div>
      <div class="card-footer">
        <a href="<?php echo e(url('simpanan')); ?>" class="btn btn-dark pull-right">Kembali</a>
      </div>
    </div>
    <?php else: ?>
    <form action="<?php echo e(url('main/transaksi/filter')); ?>" method="post" class="mt-4 mb-4" id="filter_transaksi">
      <?php ($filter=Session::get('filter_transaksi')); ?>
      <?php echo e(csrf_field()); ?>

      <div class="row">
        <div class="col-md-3">
          <select name="jenis" class="form-control select2" onchange="javascript:submit()">
            <option value="all"  >Semua Jenis Transaksi</option>
            <?php $__currentLoopData = $data['jenis-transaksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value->id); ?>" <?php echo e((!empty($filter['simpanan']) ? ($filter['simpanan']['jenis']==$value->id ? 'selected' : '') : '' )); ?>  ><?php echo e($value->jenis_transaksi); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="hidden" id="status_id" name="status" value="<?php echo e((!empty($filter['simpanan']) ? $filter['simpanan']['status'] : 'all' )); ?>">
          <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
            <option value="#282828" data-id="all">Semua Status</option>
            <?php $__currentLoopData = $data['status-transaksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value->color); ?>" <?php echo e((!empty($filter['simpanan']) ? ($filter['simpanan']['status']==$value->id ? 'selected' : '') : '' )); ?>  data-id="<?php echo e($value->id); ?>" ><?php echo e($value->status); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-md-6">
          <div>
            <div class="input-daterange input-group" data-date-format="dd-mm-yyyy" data-provide="datepicker">
              <input type="text" class="form-control" value="<?php echo e((!empty($filter['simpanan']) ? $filter['simpanan']['from'] : '' )); ?>" autocomplete="off" id="from" onchange="javascript:submit()" name="from" placeholder="Dari Tanggal" />
              <input type="text" class="form-control" value="<?php echo e((!empty($filter['simpanan']) ? $filter['simpanan']['to'] : '' )); ?>" autocomplete="off" id="to" onchange="javascript:submit()" name="to" placeholder="Sampai Tanggal" />
            </div>
          </div>
        </div>
      </div>
      <input type="hidden" name="modul" value="simpanan">
    </form>
    <hr>
    <?php if(count($data['transaksi'])==0): ?>
    <div style="width:100%;text-align:center" class="mb-5 mt-4">
      <img src="<?php echo e(asset('assets/images/not-found.png')); ?>" class="mt-3" style="width:180px">
      <h5 class="mt-3">Data Simpanan Tidak Ditemukan</h5>
    </div>
    <?php else: ?>
    <div class="table-responsive mt-4">
      <table class="table table-middle table-custom">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Jenis Transaksi</th>
            <th class="center">Metode Transaksi</th>
            <th style="text-align:right">Nominal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $data['transaksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <td class="center" style="width:1px;white-space:nowrap;border-color:<?php echo e($value->color); ?>"><?php echo e(\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')); ?></td>
              <td style="font-weight:500"><?php echo e($value->jenis_transaksi); ?></td>
              <td class="center"><?php echo e($value->metode_transaksi); ?></td>
              <td style="text-align:right;color:<?php echo e(($value->operasi=='kredit' ? '#008305' : '#be0600')); ?>"><?php echo e(($value->operasi=='kredit' ? '+' : '-')); ?> <?php echo e(number_format(str_replace('-','',$value->nominal),0,',','.')); ?></td>
              <td style="width:1px;white-space:nowrap">
                <div class="text-center">
                  <a href="<?php echo e(url('anggota/detail?anggota='.$data['anggota']->no_anggota.'&tab='.$tab.'&id='.$value->id)); ?>" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                </div>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
    <div class="mb-4">
      <?php echo e($data['transaksi']->links('include.pagination', ['pagination' => $data['transaksi']] )); ?>

    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
  <div class="col-md-3">
    <div class="mb-4 pt-4">
      <div class="form-group">
        <p class="text-muted text-truncate mb-2">Saldo Simpanan Pokok</p>
        <div class="font-size-15">Rp <?php echo e(number_format($data['anggota']->simpanan_pokok,0,',','.')); ?></div>
      </div>
      <div class="form-group">
        <p class="text-muted text-truncate mb-2">Saldo Simpanan Wajib</p>
        <div class="font-size-15">Rp <?php echo e(number_format($data['anggota']->simpanan_wajib,0,',','.')); ?></div>
      </div>
      <div class="form-group">
        <p class="text-muted text-truncate mb-2">Saldo Simpanan Hari Raya</p>
        <div class="font-size-15">Rp <?php echo e(number_format($data['anggota']->simpanan_hari_raya,0,',','.')); ?></div>
      </div>
      <div class="form-group">
        <p class="text-muted text-truncate mb-2">Saldo Simpanan Sukarela</p>
        <div class="font-size-15">Rp <?php echo e(number_format($data['anggota']->simpanan_sukarela,0,',','.')); ?></div>
      </div>
      <div class="form-group">
        <p class="text-muted text-truncate mb-2">Total Saldo Simpanan</p>
        <div class="font-size-15">Rp <?php echo e(number_format($data['anggota']->total_simpanan,0,',','.')); ?></div>
      </div>
    </div>
  </div>
</div>
<?php /**PATH /var/www/html/resources/views/anggota/detail/simpanan.blade.php ENDPATH**/ ?>