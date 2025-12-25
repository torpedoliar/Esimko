@php
  $page='Pinjaman';
  $subpage='Pinjaman';
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
    <div class="col-md-9">
      <div class="card">
        <div class="card-body">
          <h5>Formulir Pinjaman</h5>
          <hr>
          <form action="{{url('transaksi/proses')}}" id="proses_transkasi" method="post">
            {{ csrf_field() }}
            <div class="row mt-3">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Jenis Pinjaman</label>
                  <select name="jenis_transaksi" onchange="calc_angsuran()" id="jenis_pinjaman" class="form-control select2">
                    @foreach ($data['jenis-transaksi'] as $key => $value)
                    <option value="{{$value->id}}"  >{{$value->jenis_transaksi}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Jumlah Pinjaman (Rp)</label>
                  <input type="text" style="text-align:right" onkeyup="calc_angsuran()" id="jumlah_pinjaman" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="nominal" value="0">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="control-label">Tenor (bulan)</label>
                  <input data-toggle="touchspin" onchange="calc_angsuran()" id="tenor" name="tenor" value="1" type="text" value="0" data-max='100'>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Angsuran Pokok</label>
                  <input type="text" id="angsuran_pokok" autocomplete="off" style="text-align:right" class="form-control" readonly>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Bunga Pinjaman</label>
                  <input type="text" id="bunga_pinjaman" autocomplete="off" style="text-align:right" class="form-control" readonly>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Total Angsuran</label>
                  <input type="text" id="total_angsuran_pinjaman" autocomplete="off" style="text-align:right" class="form-control" name="total_angsuran_pinjaman" readonly>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Gaji Pokok (Rp)</label>
                  <input type="text" style="text-align:right" onkeyup="calc_angsuran()" id="gaji_pokok" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="gaji_pokok" value="{{$data['gaji-pokok'][1]}}">
                  <div style="text-align:right;color:#e30000">Update gaji pokok anda</div>
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label>Upload Slip Gaji</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFile" name="attachment">
                    <label class="custom-file-label" for="customFile">Choose file</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="keterangan" class="form-control" style="height:110px"></textarea>
            </div>
            <div id="alert"></div>
            <input type="hidden" name="modul" value="pinjaman">
            <input type="hidden" name="bulan" value="{{$data['gaji-pokok'][0]}}">
            <input type="hidden"name="metode_transaksi" value="3">
            <div class="pull-right">
              <button class="btn btn-primary" id="action" name="action" value="add">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <h5 class="mt-4 mb-3">Angsuran Bulanan</h5>
      <div class="list-content">
        <span>Angsuran Simpanan</span>
        <div class="info-content">Rp 350.000</div>
      </div>
      <div class="list-content">
        <span>Setoran Berkala</span>
        <div class="info-content">Rp {{number_format($data['setoran-berkala'],0,',','.')}}</div>
      </div>
      @foreach ($data['jenis-transaksi'] as $key => $value)
        <div class="list-content">
          <span>Angsuran {{$value->jenis_transaksi}}</span>
          <div class="info-content">Rp {{number_format($value->angsuran,0,',','.')}}</div>
        </div>
      @endforeach
      <div class="list-content">
        <span>Total Angsuran Belanja</span>
        <div class="info-content">Rp {{number_format($data['angsuran-belanja'],0,',','.')}}</div>
      </div>
      <div class="list-content">
        <span>Total Angsuran</span>
        <div class="info-content">{{number_format($data['total-angsuran'],0,',','.')}}</div>
        <input type="hidden" id="total_angsuran">
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
    angsuran_pinjaman=angsuran+bunga;

    $('#angsuran_pokok').val(accounting.formatNumber(angsuran,0,'.',','));
    $('#bunga_pinjaman').val(accounting.formatNumber(bunga,0,'.',','));
    $('#total_angsuran_pinjaman').val(accounting.formatNumber(angsuran_pinjaman,0,'.',','));

    gaji_pokok =$('#gaji_pokok').val();
    gaji_pokok = gaji_pokok.split('.').join('');

    total_angsuran={{$data['total-angsuran']}};
    $('#total_angsuran').val(accounting.formatNumber(total_angsuran,0,'.',','));
    console.log("Angsuran Pinjaman "+$data['angsuran-pinjaman']);
    console.log("Angsuran Pinjaman "+$data['angsuran-pinjaman']);
    console.log("Angsuran Pinjaman  + bunga"+angsuran_pinjaman);


    total_angsuran_pinjaman={{$data['angsuran-pinjaman']}}+{{$data['angsuran-simpanan']}}+angsuran_pinjaman;

    $.get("{{ url('api/check_sisa_pinjaman/'.Session::get('useractive')->no_anggota) }}/"+jenis,function (result) {
      if(result.sisa_tenor == 0 ){
        if(total_angsuran <= gaji_pokok ){
          if(total_angsuran_pinjaman > gaji_pokok/2){
            color='danger';
            note='Maaf anda belum bisa mengajukan pinjaman dengan total angsuran perbulan <b>Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',',')+'</b> karena melebihi 50% Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai';
            $('#action').prop('disabled', true);
          }
          else{
            color='success';
            note='Selamat anda bisa melanjutkan pengajuan pinjaman dengan total angsuran perbulan <b>Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',',')+'</b>. Silahkan lanjutkan proses pengajuan sampai pengajuan disetujui oleh petugas';
            $('#action').prop('disabled', false);
          }
        }
        else{
          color='danger';
          note='Maaf anda belum bisa mengajukan pinjaman dengan total angsuran perbulan Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',',')+' karena total angsuran melebihi Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai atau ubah kembali nominal setoran berkala';
          $("#action").prop('disabled', true);
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
