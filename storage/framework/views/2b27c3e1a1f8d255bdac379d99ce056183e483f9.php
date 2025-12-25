<div class="row mt-4 mb-4">
  <div class="col-md-7">
    <div style="padding-top:20px;padding-bottom:20px;height:100%">
      <h5 class="mb-3">Informasi Personal</h5>
      <table class="table table-informasi">
        <tr>
          <th width="200px">No. Anggota</th>
          <th width="10px">:</th>
          <td><?php echo e($data['anggota']->no_anggota); ?></td>
        </tr>
        <tr>
          <th>Nama Lengkap</th>
          <th>:</th>
          <td><?php echo e($data['anggota']->nama_lengkap); ?></td>
        </tr>
        <tr>
          <th>Tempat, Tanggal Lahir</th>
          <th>:</th>
          <td><?php echo e($data['anggota']->tempat_lahir); ?>, <?php echo e(\App\Helpers\GlobalHelper::tgl_indo($data['anggota']->tanggal_lahir)); ?></td>
        </tr>
        <tr>
          <th>Jenis Kelamin</th>
          <th>:</th>
          <td><?php echo e(($data['anggota']->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan')); ?></td>
        </tr>
        <tr>
          <th>No. KTP</th>
          <th>:</th>
          <td><?php echo e($data['anggota']->no_ktp); ?></td>
        </tr>
        <tr>
          <th>No. HIRS</th>
          <th>:</th>
          <td><?php echo e($data['anggota']->no_hirs); ?></td>
        </tr>
        <tr>
          <th>Divisi</th>
          <th>:</th>
          <td><?php echo e($data['anggota']->divisi); ?></td>
        </tr>
        <tr>
          <th>Bagian</th>
          <th>:</th>
          <td><?php echo e($data['anggota']->bagian); ?></td>
        </tr>
        <tr>
          <th>Level Jabatan</th>
          <th>:</th>
          <td><?php echo e($data['anggota']->level); ?></td>
        </tr>
        <tr>
          <th>Mulai Bekerja</th>
          <th>:</th>
          <td><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($data['anggota']->tanggal_bekerja)); ?> <span style="font-weight:500">(<?php echo e(\App\Helpers\GlobalHelper::hitung_hari($data['anggota']->tanggal_bekerja,date('Y-m-d'),'y')); ?> tahun)</span></td>
        </tr>
        <tr>
          <th>Mulai Bergabung</th>
          <th>:</th>
          <td><?php echo e(\App\Helpers\GlobalHelper::tgl_indo($data['anggota']->tanggal_bergabung)); ?> <span style="font-weight:500">(<?php echo e(\App\Helpers\GlobalHelper::hitung_hari($data['anggota']->tanggal_bergabung,date('Y-m-d'),'y')); ?> tahun)</span></td>
        </tr>
      </table>
    </div>
  </div>
  <div class="col-md-5">
    <div style="padding:20px 30px; border-left:1px solid rgb(0 0 0 / 7%);height:100%">
      <?php if(!empty($data['anggota']->no_rekening)): ?>
        <div class="alert alert-secondary mb-4">
          <div class="media">
            <img src="<?php echo e(asset('assets/images/icon-page/wallet.png')); ?>" alt="" style="height:70px;margin-right:20px" />
            <div class="media-body align-self-center">
              <div class="font-size-15" style="color:#2e2e9c"><?php echo e($data['anggota']->nama_bank); ?></div>
              <div class="font-size-18" style="font-weight:500;letter-spacing:1.5px">
                <?php echo e($data['anggota']->no_rekening); ?>

              </div>
              <div class="font-size-14 text-muted" style="font-weight:400">
                <?php echo e((!empty($data['anggota']->atas_nama) ? $data['anggota']->atas_nama : $data['anggota']->nama_lengkap )); ?>

              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <h5 class="mb-3">Informasi Kontak</h5>
      <table class="table table-informasi">
        <tr>
          <th width="130px">Alamat</th>
          <th width="5px">:</th>
          <td><?php echo e((!empty($data['anggota']->alamat) ? $data['anggota']->alamat : 'Belum Diketahui')); ?></td>
        </tr>
        <tr>
          <th>No. Handphone</th>
          <th>:</th>
          <td><?php echo e((!empty($data['anggota']->no_handphone) ? $data['anggota']->no_handphone : 'Belum Diketahui')); ?></td>
        </tr>
        <tr>
          <th>Email</th>
          <th>:</th>
          <td><?php echo e((!empty($data['anggota']->email) ? $data['anggota']->email : 'Belum Diketahui')); ?></td>
        </tr>
      </table>
      <?php if(count($data['gaji-pokok']) > 0 ): ?>
      <h5 class="mb-3 mt-4">Riwayat Gaji</h5>
      <ul class="verti-timeline list-unstyled">
        <?php $__currentLoopData = $data['gaji-pokok']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="event-list">
          <div class="event-timeline-dot">
            <i class="bx bx-right-arrow-circle"></i>
          </div>
          <h6><?php echo e(\App\Helpers\GlobalHelper::nama_bulan($value->bulan)); ?></h6>
          <p class="text-muted">Rp <?php echo e(number_format($value->gaji_pokok,0,',','.')); ?></p>
        </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php /**PATH /var/www/html/resources/views/anggota/detail/profil.blade.php ENDPATH**/ ?>