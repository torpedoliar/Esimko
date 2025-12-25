@php
  $app='master';
  $page='Pengaturan';
  $subpage='Bunga Pinjaman';
@endphp
@extends('layouts.admin')
@section('title')
  Pengaturan Bunga Pinjaman |
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/account.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Pengaturan Bunga Pinjaman</h4>
        <p class="text-muted m-0">Mengatur persentase bunga pinjaman untuk semua jenis pinjaman</p>
      </div>
    </div>
  </div>

  @if(Session::has('message'))
    <div class="alert alert-{{ Session::get('message_type') }} alert-dismissible fade show" role="alert">
      {{ Session::get('message') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  <div class="row">
    <!-- Form Pengaturan -->
    <div class="col-md-5">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <i class="fa fa-percent"></i> Pengaturan Bunga
        </div>
        <div class="card-body">
          <form action="{{ url('pengaturan/bunga_pinjaman/proses') }}" method="POST" id="formBunga">
            {{ csrf_field() }}
            
            <div class="form-group">
              <label>Bunga Pinjaman Saat Ini</label>
              <div class="input-group">
                <input type="number" step="0.01" min="0" max="100" name="nilai" id="inputBunga"
                       value="{{ ($data['pengaturan']->nilai ?? 0.01) * 100 }}" 
                       class="form-control form-control-lg" required>
                <div class="input-group-append">
                  <span class="input-group-text">% per bulan</span>
                </div>
              </div>
              <small class="text-muted">Masukkan nilai dalam persen (contoh: 1 untuk 1%)</small>
            </div>

            <!-- Preview Perhitungan -->
            <div class="card bg-light mb-3">
              <div class="card-body">
                <h6 class="card-title"><i class="fa fa-calculator"></i> Preview Perhitungan</h6>
                <p class="mb-1">Jika pinjaman <strong>Rp 10.000.000</strong> dengan bunga <strong id="previewPersen">{{ ($data['pengaturan']->nilai ?? 0.01) * 100 }}%</strong>:</p>
                <p class="mb-0">Bunga per bulan = <strong id="previewBunga">Rp {{ number_format(10000000 * ($data['pengaturan']->nilai ?? 0.01), 0, ',', '.') }}</strong></p>
              </div>
            </div>

            <div class="form-group">
              <label>Keterangan Perubahan</label>
              <textarea name="keterangan" class="form-control" rows="2" 
                        placeholder="Contoh: Penyesuaian bunga sesuai kebijakan baru"></textarea>
            </div>

            <input type="hidden" name="password" id="adminPassword">
            
            <button type="button" onclick="showPasswordModal()" class="btn btn-primary btn-block btn-lg">
              <i class="fa fa-save"></i> Simpan Perubahan
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Riwayat Perubahan -->
    <div class="col-md-7">
      <div class="card">
        <div class="card-header bg-secondary text-white">
          <i class="fa fa-history"></i> Riwayat Perubahan (Audit Trail)
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead class="thead-light">
                <tr>
                  <th>Tanggal</th>
                  <th>Nilai Lama</th>
                  <th>Nilai Baru</th>
                  <th>Diubah Oleh</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                @forelse($data['logs'] as $log)
                <tr>
                  <td>{{ \App\Helpers\GlobalHelper::tgl_indo($log->created_at) }}</td>
                  <td><span class="badge badge-secondary">{{ $log->nilai_lama * 100 }}%</span></td>
                  <td><span class="badge badge-primary">{{ $log->nilai_baru * 100 }}%</span></td>
                  <td>{{ $log->nama_admin }}</td>
                  <td>{{ $log->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="fa fa-info-circle fa-2x mb-2"></i><br>
                    Belum ada riwayat perubahan
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Password Confirmation Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fa fa-lock"></i> Konfirmasi Password Admin</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Untuk keamanan, masukkan password Anda untuk mengkonfirmasi perubahan ini.</p>
        <div class="form-group">
          <label>Password</label>
          <input type="password" id="passwordInput" class="form-control form-control-lg" 
                 placeholder="Masukkan password Anda" autofocus>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" onclick="submitWithPassword()" class="btn btn-primary">
          <i class="fa fa-check"></i> Konfirmasi & Simpan
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
  // Preview calculation
  $('#inputBunga').on('input', function() {
    var persen = parseFloat($(this).val()) || 0;
    var nominal = 10000000;
    var bunga = nominal * (persen / 100);
    
    $('#previewPersen').text(persen + '%');
    $('#previewBunga').text('Rp ' + bunga.toLocaleString('id-ID'));
  });

  function showPasswordModal() {
    $('#passwordInput').val('');
    $('#passwordModal').modal('show');
    setTimeout(function() {
      $('#passwordInput').focus();
    }, 500);
  }

  function submitWithPassword() {
    var password = $('#passwordInput').val();
    if (!password) {
      alert('Password tidak boleh kosong!');
      return;
    }
    $('#adminPassword').val(password);
    $('#passwordModal').modal('hide');
    $('#formBunga').submit();
  }

  // Allow Enter key to submit in modal
  $('#passwordInput').keypress(function(e) {
    if (e.which == 13) {
      submitWithPassword();
    }
  });
</script>
@endsection
