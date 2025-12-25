@php
  $page='Register';
  $subpage='Register';
@endphp
@extends('layouts.landing_page')
@section('title')
  Register |
@endsection
@section('content')
<div class="container-fluid">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h4 class="mb-3">Formulir Anggota Baru</h4>
          <hr>
          <form class="form-horizontal" action="{{url('auth/register/proses')}}" method="post">
            {{ csrf_field() }}
            <div class="row">
              <div class="col-auto">
                <div class="avatar-wrapper" data-tippy-placement="bottom" title="Change Avatar">
                  <img id="modal_avatar" src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" />
                  <div class="upload-button" onclick="changeImage('avatar')"></div>
                  <input class="file-upload" type="file" name="avatar" accept="image/*"/>
                </div>
              </div>
              <div class="col">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>NIK / No. KTP</label>
                      <input type="text" class="form-control" name="no_ktp" required >
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="form-group">
                      <label>Nama Lengkap</label>
                      <input type="text" class="form-control" name="nama_lengkap" required >
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Tempat Lahir</label>
                      <input type="text" class="form-control" name="tempat_lahir" autocomplete="off" >
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Tanggal Lahir</label>
                      <input type="text" class="form-control datepicker" name="tanggal_lahir" autocomplete="off" >
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Jenis Kelaminr</label>
                      <select class="select2" name="jenis_kelamin" style="width:100%" >
                        <option value="L" >Laki-Laki</option>
                        <option value="P" >Perempuan</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Divisi</label>
                      <input type="text" class="form-control" name="divisi" required >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Bagian</label>
                      <input type="text" class="form-control" name="bagian" required >
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Level Jabatan</label>
                      <input type="text" class="form-control" name="level" required >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Lokasi Kerja</label>
                      <select class="select2 form-control" name="lokasi_kerja">
                        <option value="SJA-1">SJA 1</option>
                        <option value="SJA-3">SJA 3</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>No. HIRS</label>
                      <input type="text" class="form-control" name="no_hirs" required >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>ID. Karyawan</label>
                      <input type="text" class="form-control" name="id_karyawan" required >
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>No. Rekening</label>
                      <input type="text" class="form-control" name="no_rekening" >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Nama Bank</label>
                      <input type="text" class="form-control" name="nama_bank" >
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Email</label>
                      <input type="text" class="form-control" name="email" >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>No Handphone</label>
                      <input type="text" class="form-control" name="no_handphone" >
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Alamat</label>
                  <textarea class="form-control" name="alamat" ></textarea>
                </div>
                <hr class="mt-5">
                <div class="pull-right">
                  <a class="btn btn-dark waves-effect waves-light" href="{{url('')}}">KEMBALI</a>
                  <button class="btn btn-primary waves-effect waves-light" type="submit">DAFTAR ANGGOTA</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
<script>
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
@endsection
