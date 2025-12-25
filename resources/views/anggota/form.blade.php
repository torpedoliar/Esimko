@php
  $app='master';
  $page='Data Anggota';
  $subpage='Data Anggota';
@endphp
@extends('layouts.admin')
@section('title')
  Data Anggota |
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/profile.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Data Anggota</h4>
        <p class="text-muted m-0">Formulir pendaftaran anggota koperasi yang dilakukan oleh petugas</p>
      </div>
    </div>
  </div>
  <form action="{{url('anggota/proses')}}" style="margin-top:30px" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="card">
      <div class="card-header">
        <h5>{{($action=='add' ? 'Tambah' : 'Edit')}} Anggota</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-auto">
            <div class="avatar-wrapper" data-tippy-placement="bottom" title="Change Avatar" style="width:225px;height:225px">
              <img id="modal_avatar" src="{{(!empty($data['anggota']->avatar) ? asset('storage/'.$data['anggota']->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" />
              <div class="upload-button" onclick="changeImage('avatar')"></div>
              <input class="file-upload" type="file" name="avatar" accept="image/*"/>
            </div>
          </div>
          <div class="col">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>No. Anggota <br> (No. Terakhir {{ $data['no_anggota'][0] . ' | ' . $data['no_anggota'][1] }})</label>
                  <input type="text" class="form-control" name="no_anggota" value="{{(!empty($data['anggota']) ? $data['anggota']->no_anggota : '')}}"  autocomplete="off"  >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>No. KTP</label>
                  <input type="text" class="form-control" name="no_ktp" value="{{(!empty($data['anggota']) ? $data['anggota']->no_ktp : '')}}"  autocomplete="off" >
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Lengkap</label>
                  <input type="text" class="form-control" value="{{(!empty($data['anggota']) ? $data['anggota']->nama_lengkap : '')}}" name="nama_lengkap" autocomplete="off" >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Tempat Lahir</label>
                  <input type="text" class="form-control" name="tempat_lahir" autocomplete="off" value="{{(!empty($data['anggota']) ? $data['anggota']->tempat_lahir : '')}}" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Tanggal Lahir</label>
                  <input type="text" class="datepicker form-control" value="{{(!empty($data['anggota']) ? \App\Helpers\GlobalHelper::dateFormat($data['anggota']->tanggal_lahir,'d-m-Y') : '')}}" name="tanggal_lahir" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Jenis Kelamin</label>
                  <select class="select2" name="jenis_kelamin" style="width:100%">
                    <option value="L" >Laki-Laki</option>
                    <option value="P" >Perempuan</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Nama Panggilan</label>
                  <input type="text" class="form-control" name="nama_panggilan" value="{{(!empty($data['anggota']) ? $data['anggota']->nama_panggilan : '')}}"  autocomplete="off"  >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>No. HIRS</label>
                  <input type="text" class="form-control" name="no_hirs" value="{{(!empty($data['anggota']) ? $data['anggota']->no_hirs : '')}}"  autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>ID. Karyawan</label>
                  <input type="text" class="form-control" name="id_karyawan" value="{{(!empty($data['anggota']) ? $data['anggota']->id_karyawan : '')}}" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Lokasi Kerja</label>
                  <select class="select2 form-control" name="lokasi_kerja">
                    <option value="SJA-1">SJA 1</option>
                    <option value="SJA-3">SJA 3</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Level Jabatan</label>
                  <input type="text" class="form-control" name="level" value="{{(!empty($data['anggota']) ? $data['anggota']->level : '')}}" autocomplete="off" >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Tanggal Bergabung</label>
                  <input type="text" class="datepicker form-control" name="tanggal_bergabung" value="{{(!empty($data['anggota']) ? \App\Helpers\GlobalHelper::dateFormat($data['anggota']->tanggal_bergabung,'d-m-Y') : '')}}" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-9">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Divisi</label>
                      <input type="text" class="form-control" name="divisi" value="{{(!empty($data['anggota']) ? $data['anggota']->divisi : '')}}" autocomplete="off" >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Bagian</label>
                      <input type="text" class="form-control" name="bagian" value="{{(!empty($data['anggota']) ? $data['anggota']->bagian : '')}}" autocomplete="off" >
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Email</label>
                  <input type="text" class="form-control" name="email" autocomplete="off" value="{{(!empty($data['anggota']) ? $data['anggota']->email : '')}}" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>No Handphone</label>
                  <input type="text" class="form-control" name="no_handphone" value="{{(!empty($data['anggota']) ? $data['anggota']->no_handphone : '')}}" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>No. Rekening</label>
                  <input type="text" class="form-control" name="no_rekening" value="{{(!empty($data['anggota']) ? $data['anggota']->no_rekening : '')}}" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Nama Bank</label>
                  <input type="text" class="form-control" name="nama_bank" value="{{(!empty($data['anggota']) ? $data['anggota']->nama_bank : '')}}"  autocomplete="off" >
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Alamat</label>
              <textarea class="form-control" name="alamat" autocomplete="off" style="height:70px" >{{(!empty($data['anggota']) ? $data['anggota']->alamat : '')}}</textarea>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tambahan Akses</label>
                  <select class="select2" name="hak_akses[]" multiple style="width:100%"  >
                    @foreach ($data['hak-akses'] as $key => $value)
                    <option value="{{$value->id}}" {{$value->selected}} >{{$value->hak_akses}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Password</label>
                  <input type="text" class="form-control" name="password" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Status Anggota</label>
                  <select class="select2" name="status_anggota" style="width:100%">
                    @foreach ($data['status-anggota'] as $key => $value)
                    <option value="{{$value->id}}" {{(!empty($data['anggota']) && $data['anggota']->fid_status == $value->id ? 'selected' : '')}} >{{$value->status_anggota}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <input type="hidden" name="action" value="{{$action}}">
        <input type="hidden" name="id" value="{{$id}}">
        <div class="pull-right">
          <a class="btn btn-secondary" href="{{url('anggota')}}" >Kembali</a>
          <button class="btn btn-primary" type="submit">{{($action=='add' ? 'Tambah' : 'Simpan')}}</button>
        </div>
      </div>
    </div>
  </form>
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
