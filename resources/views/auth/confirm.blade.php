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
          <div class="center mb-5">
            <img src="{{asset('assets/images/success.png')}}" style="width:80px">
            <h4 class="mt-3">Daftar Anggota Baru Berhasil Disimpan</h4>
            <p>Terimakasih sudah melakukan pendaftaran anggota baru, Silahkan melakukan konfirmasi langsung di Kantor Koperasi Satya Sejahtera dengan membawa syarat-syarat yang sudah ditetapkan</p>
          </div>
          <h5 class="mb-3">#Informasi Anggota</h5>
          <table class="table table-informasi">
            <tr>
              <th width="180px">No. Anggota</th>
              <th width="10px">:</th>
              <td>{{$data->no_anggota}}</td>
            </tr>
            <tr>
              <th>Nama Lengkap</th>
              <th>:</th>
              <td>{{$data->nama_lengkap}}</td>
            </tr>
            <tr>
              <th>Tempat, Tanggal Lahir</th>
              <th>:</th>
              <td>{{$data->tempat_lahir}}, {{\App\Helpers\GlobalHelper::tgl_indo($data->tanggal_lahir)}}</td>
            </tr>
            <tr>
              <th>No KTP</th>
              <th>:</th>
              <td>{{$data->no_ktp}}</td>
            </tr>
            <tr>
              <th>ID Karyawan / NIK</th>
              <th>:</th>
              <td>{{$data->id_karyawan}}</td>
            </tr>
            <tr>
              <th>HIRS</th>
              <th>:</th>
              <td>{{$data->no_hirs}}</td>
            </tr>
          </table>
          <h5 class="mb-3 mt-3">#Syarat Pendaftaran Anggota</h5>
          <ol>
            <li>Pas Foto ukuran 3 x 3 sebanyak 2 lembar</li>
            <li>Fotocopy KTP sebanyak 1 lembar</li>
            <li>Fotocopy ID Card sebanyak 1 lembar</li>
            <li>Fotocopy Slip Gaji sebanyak 1 lembar</li>
            <li>Fotocopy SK/PKWTT sebanyak 1 lembar</li>
          </ol>
        </div>
        <div class="card-footer">
          <div class="pull-right">
            <a href="{{url('auth/register/confirm?print=y')}}" target="_blank" class="btn btn-primary">Cetak Formulir</a>
            <a href="{{url('')}}" class="btn btn-dark">Kembali</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
