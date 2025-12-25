@php
  $page='Pinjaman';
  $subpage='Pinjaman';
  $disabled=($data['pinjaman']->fid_status >= 3 || $data['pinjaman']->fid_status=='6'  ? 'disabled' : '');
@endphp
@extends('layouts.main')
@section('title')
Pinjaman |
@endsection
@section('css')
  <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box pb-0">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/save-money.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Pinjaman</h4>
        <p class="text-muted m-0">Formulir pengajuan pinjaman yang sudah diinput oleh anggota</p>
      </div>
    </div>
  </div>
  <div class="row mt-4 mb-4">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <div class="center mb-3">
            <img src="{{asset('assets/images/'.$data['pinjaman']->icon)}}" style="width:80px">
            <h4 class="mt-3">{{$data['keterangan']->label}}</h4>
            <p>{{$data['keterangan']->keterangan}}</p>
          </div>
        </div>
        <div class="card-header" style="background:#eaecef">
          <ul class="nav nav-pills" role="tablist">
            <li class="nav-item waves-effect waves-light">
              <a class="nav-link active" data-toggle="tab" href="#informasi" role="tab">
                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                <span class="d-none d-sm-block">Informasi Pinjaman</span>
              </a>
            </li>
            <li class="nav-item waves-effect waves-light">
              <a class="nav-link" data-toggle="tab" href="#angsuran" role="tab">
                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                <span class="d-none d-sm-block">Angsuran Pinjaman</span>
              </a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <div class="tab-pane active" id="informasi" role="tabpanel">
              <h5 class="mb-3">Informasi Transaksi</h5>
              <table class="table table-informasi">
                <tr>
                  <th width="180px">No. Anggota</th>
                  <th width="10px">:</th>
                  <td>{{$data['pinjaman']->no_anggota}}</td>
                </tr>
                <tr>
                  <th>Nama Lengkap</th>
                  <th>:</th>
                  <td>{{$data['pinjaman']->nama_lengkap}}</td>
                </tr>
                <tr>
                  <th>Jenis Pinjaman</th>
                  <th>:</th>
                  <td>{{$data['pinjaman']->jenis_transaksi}}</td>
                </tr>
                <tr>
                  <th>Metode Pencairan</th>
                  <th>:</th>
                  <td>{{$data['pinjaman']->metode_transaksi}}</td>
                </tr>
                <tr>
                  <th>Jumlah Pinjaman</th>
                  <th>:</th>
                  <td>Rp {{number_format(str_replace('-','',$data['pinjaman']->nominal),0,',','.')}}</td>
                </tr>
                <tr>
                  <th>Total Angsuran</th>
                  <th>:</th>
                  <td>Rp {{number_format(str_replace('-','',$data['pinjaman']->total_angsuran),0,',','.')}} / Bulan</td>
                </tr>
                <tr>
                  <th>Sisa Pinjaman</th>
                  <th>:</th>
                  <td>Rp {{number_format(str_replace('-','',$data['pinjaman']->sisa_pinjaman),0,',','.')}}</td>
                </tr>
                <tr>
                  <th>Sisa Tenor</th>
                  <th>:</th>
                  <td>{{$data['pinjaman']->sisa_tenor}} dari {{$data['pinjaman']->tenor}}</td>
                </tr>
                <tr>
                  <th>Keterangan</th>
                  <th>:</th>
                  <td>{{$data['pinjaman']->keterangan}}</td>
                </tr>
              </table>
              <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
              <ul class="verti-timeline list-unstyled">
                <li class="event-list">
                  <div class="event-timeline-dot">
                    <i class="bx bx-right-arrow-circle"></i>
                  </div>
                  <h6>{{\App\Helpers\GlobalHelper::tgl_indo($data['pinjaman']->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($data['pinjaman']->created_at,'H:i:s')}}</h6>
                  <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500">{{$data['pinjaman']->nama_petugas}}</span></p>
                </li>
                @foreach (\App\Helpers\GlobalHelper::get_verifikasi_transaksi($id,'transaksi') as $key => $value)
                <li class="event-list">
                  <div class="event-timeline-dot">
                    <i class="bx bx-right-arrow-circle"></i>
                  </div>
                  <h6>{{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}</h6>
                  <p class="text-muted">{{$value->caption}} <span style="font-weight:500">{{$value->nama_lengkap}}</span></p>
                </li>
                @endforeach
              </ul>
            </div>
            <div class="tab-pane" id="angsuran" role="tabpanel">
              <table class="table table-middle table-bordered table-hover mt-3">
                <thead class="thead-light">
                  <tr>
                    <th style="width:1px;white-space:nowrap">Ke</th>
                    <th style="text-align:right">Sisa<br>Pinjaman</th>
                    <th class="center">Bunga<br>(%)</th>
                    <th style="text-align:right">Angsuran<br>Pokok</th>
                    <th style="text-align:right">Angsuran<br>Bunga</th>
                    <th style="text-align:right">Total<br>Angsuran</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($data['angsuran'] as $key => $value)
                    <tr>
                      <td>{{$value->angsuran_ke}}</td>
                      <td style="text-align:right">{{number_format($value->sisa_hutang,'0',',','.')}}</td>
                      <td class="center">{{$value->bunga}}</td>
                      <td style="text-align:right">{{number_format($value->angsuran_pokok,'0',',','.')}}</td>
                      <td style="text-align:right">{{number_format($value->angsuran_bunga,'0',',','.')}}</td>
                      <td style="text-align:right">{{number_format($value->angsuran_pokok+$value->angsuran_bunga,'0',',','.')}}</td>
                      <td class="center" style="width:1px;white-space:nowrap">
                        <span style="background:{{$value->color}};padding:3px 6px;color:#fff;font-size:11px">{{$value->status_angsuran}}</span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <a href="{{url('main/pinjaman')}}" class="btn btn-dark pull-right">Kembali</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h5>Edit Data Pinjaman</h5>
          <hr>
          <form action="{{url('main/transaksi/proses')}}" method="post">
            {{ csrf_field() }}
            <div class="form-group">
              <label>Jenis Pinjaman</label>
              <select name="jenis_transaksi" {{$disabled}} onchange="calc_angsuran()" id="jenis_pinjaman" class="form-control select2">
                @foreach ($data['jenis-transaksi'] as $key => $value)
                <option value="{{$value->id}}" {{($data['pinjaman']->fid_jenis_transaksi==$value->id ? 'selected' : '')}}  >{{$value->jenis_transaksi}}</option>
                @endforeach
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nominal Pinjaman</label>
                  <input type="text" {{$disabled}} style="text-align:right" onkeyup="calc_angsuran()" id="jumlah_pinjaman" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="nominal" value="{{str_replace('-','',$data['pinjaman']->nominal)}}">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label">Tenor</label>
                  <input data-toggle="touchspin" {{$disabled}} onchange="calc_angsuran()" id="tenor" name="tenor" value="{{$data['pinjaman']->tenor}}" type="text" value="0" data-max='100' >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Angsuran Pokok</label>
                  <input type="text" id="angsuran_pokok" {{$disabled}} autocomplete="off" style="text-align:right" class="form-control" readonly>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Bunga Pinjaman</label>
                  <input type="text" id="bunga_pinjaman" {{$disabled}} autocomplete="off" style="text-align:right" class="form-control" readonly>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Total Angsuran</label>
                  <input type="text" id="total_angsuran_pinjaman" {{$disabled}} autocomplete="off" style="text-align:right" class="form-control" readonly>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Gaji Pokok (Rp)</label>
                  <input type="text" style="text-align:right" {{$disabled}} onkeyup="calc_angsuran()" id="gaji_pokok" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="gaji_pokok" value="{{$data['gaji-pokok'][1]}}">
                  <div style="text-align:right;color:#e30000;font-size:11px">Update gaji pokok anda</div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Upload Slip Gaji</label>
              <div class="custom-file">
                <input type="file" class="custom-file-input" {{$disabled}} id="customFile" name="attachment">
                <label class="custom-file-label" {{$disabled}} for="customFile">Choose file</label>
              </div>
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="keterangan" {{$disabled}} class="form-control" style="height:80px"></textarea>
            </div>
            @if(in_array($data['pinjaman']->fid_status,array('3','4','6')))
              <div class="alert alert-warning mt-2 mb-0">
                <p class="text-muted m-0">Maaf anda sudah tidak bisa mengubah atau membatalkan transaksi ini, karena sudah disetujui oleh petugas koperasi</p>
              </div>
            @elseif($data['pinjaman']->fid_status == 5 )
              <div class="alert alert-warning mt-2 mb-0">
                <p class="text-muted m-0">Maaf anda sudah tidak bisa mengubah atau melanjutkan proses transaksi ini, karena transaksi ini sudah dibatalkan</p>
              </div>
            @else
            <div id="alert"></div>
            <input type="hidden" name="id" value="{{$id}}">
            <input type="hidden" name="bulan" value="{{$data['gaji-pokok'][0]}}">
            <input type="hidden" name="modul" value="pinjaman">
            <input type="hidden"name="metode_transaksi" value="3">
            <div class="pull-right">
              <button class="btn btn-danger" type="button" name="button" onclick="batalkan()">Batalkan Transaksi</button>
              <button class="btn btn-primary" name="action" value="{{($data['pinjaman']->fid_status == 1 ? 'edit' : 'proses_ulang')}}">{{($data['pinjaman']->fid_status == 1 ? 'Edit Transaksi' : 'Ajukan Ulang')}}</button>
            </div>
            @endif
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
  <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
  <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
  <script src="{{asset('assets/js/accounting.js')}}"></script>
  <script>
  function calc_angsuran(){
    jenis = $('#jenis_pinjaman').val();

    pinjaman = $('#jumlah_pinjaman').val();
    pinjaman = pinjaman.split('.').join('');

    tenor = $('#tenor').val();
    tenor = tenor.split('.').join('');

    bunga = pinjaman*0.01;
    angsuran = (pinjaman/tenor);
    total_angsuran_pinjaman=angsuran+bunga;
    $('#angsuran_pokok').val(accounting.formatNumber(angsuran,0,'.',','));
    $('#bunga_pinjaman').val(accounting.formatNumber(bunga,0,'.',','));
    $('#total_angsuran_pinjaman').val(accounting.formatNumber(total_angsuran_pinjaman,0,'.',','));

    gaji_pokok =$('#gaji_pokok').val();
    gaji_pokok = gaji_pokok.split('.').join('');

    total_angsuran={{$data['total-angsuran']}}+total_angsuran_pinjaman;
    $.get("{{ url('api/check_sisa_pinjaman/'.Session::get('useractive')->no_anggota) }}/"+jenis,function (result) {
      if(result.sisa_tenor == 0 ){
        if(total_angsuran > gaji_pokok/2){
          color='danger';
          note='Maaf anda tidak bisa mengubah pengajuan pinjaman dengan total angsuran perbulan <b>Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',',')+'</b> karena melebihi 50% Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai';
          $('#action').prop('disabled', true);
        }
        else{
          color='success';
          note='Silahkan ubah atau batalkan pengajuan pinjaman anda dengan total angsuran perbulan <b>Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',',')+'</b>';
          $('#action').prop('disabled', false);
        }
      }
      else{
        color='danger';
        note='Maaf anda belum bisa mengajukan '+result.jenis_pinjaman+', karena anda masih mempunyai sisa angsuran '+result.jenis_pinjaman+' senilai <b>Rp '+accounting.formatNumber(result.sisa_angsuran,0,'.',',')+'</b> dan masih tersisa <b>'+result.sisa_tenor+'x angsuran</b>. Silahkan melunasi pinjaman anda atau melakukan pengajuan pinjaman yang lain.';
        $('#action').prop('disabled', true);
      }
      alert='<div class="alert alert-'+color+'" role="alert">'+note+'</div>';
      $('#alert').html(alert);
    });
  }
  calc_angsuran();
  </script>
@endsection
