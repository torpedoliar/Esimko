@php
  $app='pos';
  $page='Belanja '.ucfirst($jenis);
  $subpage='Belanja '.ucfirst($jenis);
@endphp
@extends('layouts.admin')
@section('title')
  {{$page}} |
@endsection
@section('css')
  <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
  <style>
  .list-anggota{
    padding-bottom:10px;
    border-bottom: 1px solid #f2f2f2;
    margin-top:10px;
    cursor: pointer;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/shopping-cart.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">{{$page}}</h4>
        <p class="text-muted m-0">Formulir pengajuan belanja {{$jenis}} anggota</p>
      </div>
    </div>
  </div>
  <form action="{{url('pos/belanja/'.$jenis.'/proses')}}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="card">
      <div class="card-header">
        <h5>{{($action=='add' ? 'Tambah' : 'Edit')}} Belanja Konsinyasi</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-5">
            <div style="border:#dfe4e9 dashed 2px ;padding:20px">
              <h5 class="mb-3"># Identitas Anggota</h5>
              <div class="row">
                <div class="col-auto">
                  <div class="avatar-wrapper" style="height:100px;width:100px">
                    <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" />
                  </div>
                </div>
                <div class="col">
                  <div class="list-content">
                    <span>No. Anggota</span>
                    <div id="no_anggota" class="info-content">{!!(!empty($data['belanja']) ? $data['belanja']->no_anggota :'<hr>')!!}</div>
                  </div>
                  <div class="list-content">
                    <span>Nama Lengkap</span>
                    <div id="nama_lengkap" class="info-content">{!!(!empty($data['belanja']) ? $data['belanja']->nama_lengkap :'<hr>')!!}</div>
                  </div>
                </div>
              </div>
              <h5 class="mb-3 mt-4">#Angsuran Bulanan</h5>
              <div class="list-content">
                <span>Angsuran Belanja Toko</span>
                <div id="angsuran_belanja_toko" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Angsuran Belanja Konsinyasi</span>
                <div id="angsuran_belanja_konsinyasi" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Angsuran Belanja Online</span>
                <div id="angsuran_belanja_online" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Total Angsuran Belanja</span>
                <div id="total_angsuran_belanja" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Angsuran Simpanan</span>
                <div id="angsuran_simpanan" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Angsuran Pinjaman</span>
                <div id="angsuran_pinjaman" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Setoran Berkala</span>
                <div id="setoran_berkala" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Total Angsuran</span>
                <div id="total_angsuran" class="info-content"><hr></div>
              </div>
              <input type="hidden" id="anggota_id" value="0">
              <input type="hidden" name="fid_anggota" value="{{(!empty($data['belanja']) ? $data['belanja']->no_anggota : '')}}" id="fid_anggota">
              <button type="button" onclick="pilih_anggota('show')" class="btn btn-secondary btn-block mt-3">PILIH ANGGOTA</button>
            </div>
          </div>
          <div class="col-md-7">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tanggal</label>
                  <input type="text" id="tanggal" value="{{(!empty($data['belanja']) ? \App\Helpers\GlobalHelper::dateFormat($data['belanja']->tanggal,'d-m-Y') : date('d-m-Y'))}}" class="form-control datepicker" name="tanggal" autocomplete="off" name="tanggal" >
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label>No Transaksi</label>
                  <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->no_transaksi : '')}}" class="form-control" autocomplete="off" name="no_transaksi" >
                </div>
              </div>
            </div>
            @if($jenis=='konsinyasi')
            @else
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Marketplace Platform</label>
                  <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->marketplace : '')}}" class="form-control" autocomplete="off" name="marketplace" >
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label>Nama Toko</label>
                  <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->nama_toko : '')}}" class="form-control" name="nama_toko" >
                </div>
              </div>
            </div>
            @endif
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Total Belanja</label>
                  <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->total_pembayaran : '0')}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="total_belanja" id="total_belanja" onkeyup="calc_belanja()" >
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Margin (%)</label>
                  <input type="text" data-toggle="touchspin" value="{{(!empty($data['belanja']->margin) ? $data['belanja']->margin : 10)}}" class="form-control" name="margin" id="margin" onkeyup="calc_belanja()">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Nominal Margin (Rp)</label>
                  <input type="text" style="text-align:right" class="form-control" id="nominal_margin"  readonly >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Total Pembayaran (Rp)</label>
                  <input type="text" style="text-align:right" class="form-control" name="total_pembayaran" id="total_pembayaran"  readonly >
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tenor</label>
                  <input type="text" data-toggle="touchspin" value="{{(!empty($data['belanja']->tenor) ? $data['belanja']->tenor : 1)}}" class="form-control" name="tenor" id="tenor" onkeyup="calc_belanja()" >
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Angsuran</label>
                  <input type="text" style="text-align:right" value="{{(!empty($data['belanja']) ? $data['belanja']->angsuran : '')}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="angsuran" id="angsuran" onkeyup="calc_belanja()">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Update Gaji Pokok</label>
                  <input type="text" id="gaji_pokok" onchange="calc_angsuran()" style="text-align:right" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="gaji_pokok" >
                  <div id="bulan_gaji" style="text-align:right;margin-top:5px"></div>
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
              <textarea class="form-control" name="keterangan" style="height:65px" value="">{{(!empty($data['belanja']) ? $data['belanja']->keterangan : '')}}</textarea>
            </div>
            <div id="alert"></div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <input type="hidden" name="action" value="{{$action}}">
        <input type="hidden" name="id" value="{{$id}}">
        <div class="pull-right">
          <a class="btn btn-secondary" href="{{url('pos/belanja_konsinyasi')}}" >Kembali</a>
          <button class="btn btn-primary" id="action" type="submit">{{($action=='add' ? 'Tambah' : 'Simpan')}}</button>
        </div>
      </div>
    </div>
  </form>
</div>
<div id="modal-anggota" class="modal fade right">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Pilih Anggota</h5>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <input type="text" class="form-control" value="" id="search" name="search" placeholder="Cari Anggota">
          <div class="input-group-append">
            <button class="btn btn-dark" id="btn-search" onclick="search_anggota()">Search</button>
          </div>
        </div>
        <div id="loading"><img src="{{asset('assets/images/loading.gif')}}" style="width:100px"></div>
        <div id="list-anggota" ></div>
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

  $("#tanggal").change(function(){
    anggota_id = $('#anggota_id').val();
    tanggal=$('#tanggal').val();
    bulan_sekarang=tanggal.substr(-7);
    console.log(anggota_id);
    if(anggota_id != 0 ){
      $.get("{{ url('api/find_anggota') }}/"+anggota_id+"/"+bulan_sekarang,function(result){
        $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
        $('#bulan').val(result.bulan);
        $('#bulan_gaji').html('Bulan '+result.bulan_tampil);
      });
    }
  });

  calc_belanja();
  function calc_belanja(){
    total_belanja=$('#total_belanja').val();
    total_belanja=total_belanja.split('.').join('');
    tenor=$('#tenor').val();
    margin=$('#margin').val();
    nominal_margin=total_belanja*margin/100;
    $('#nominal_margin').val(accounting.formatNumber(nominal_margin,0,'.',','));
    total_pembayaran=nominal_margin+parseInt(total_belanja);
    $('#total_pembayaran').val(accounting.formatNumber(total_pembayaran,0,'.',','));
    angsuran=total_pembayaran/tenor;
    $('#angsuran').val(accounting.formatNumber(angsuran,0,'.',','));

    anggota_id = $('#anggota_id').val();
    tanggal=$('#tanggal').val();
    bulan_sekarang=tanggal.substr(-7);

    if(anggota_id != 0 ){
      $.get("{{ url('api/find_anggota') }}/"+anggota_id+"/"+bulan_sekarang,function(result){

        gaji_pokok =$('#gaji_pokok').val();
        gaji_pokok = gaji_pokok.split('.').join('');

        total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_online+result.angsuran_belanja_konsinyasi+angsuran;
        total_angsuran_pinjaman=result.angsuran_jangka_panjang+result.angsuran_jangka_pendek+result.angsuran_barang;
        total_angsuran=total_angsuran_belanja+total_angsuran_pinjaman+result.setoran_berkala+350000;

        if(total_angsuran <= gaji_pokok ){
          if(total_angsuran_belanja > 1500000){
            color='danger';
            note='Maaf '+result.nama_lengkap+' belum bisa melakukan pengajuan belanja {{$jenis}} dengan total angsuran Rp '+accounting.formatNumber(angsuran,0,'.',',')+', karena angsuran kredit belanja melebihi limit Rp 1.500.000';
            $("#action").prop('disabled', false);
          }
          else{
            color='success';
            note='Selamat '+result.nama_lengkap+' bisa bisa melakukan pengajuan belanja {{$jenis}} dengan total angsuran Rp '+accounting.formatNumber(angsuran,0,'.',',');
            $("#action").prop('disabled', false);
          }
        }
        else{
          color='danger';
          note='Maaf '+result.nama_lengkap+' belum bisa melakukan pengajuan kredit belanja {{$jenis}} dengan total angsuran Rp '+accounting.formatNumber(angsuran,0,'.',',')+' karena total angsuran melebihi Gaji Pokok. Silahkan masukkan jumlah kredit dan tenor yang sesuai atau ubah kembali nominal setoran berkala';
          $("#action").prop('disabled', true);
        }
        alert='<div class="alert alert-'+color+'" role="alert">'+note+'</div>';
        $('#alert').html(alert);
      });
    }
  }

  function search_anggota(){
    var search = $('#search').val();
    if(search !== ''){ search = '/'+search }
    else{ search = '/all'}
    $('#loading').show();
    $('#list-anggota').hide();
    $.get("{{ url('api/get_anggota/aktif/') }}"+search,function (result) {
      $('#list-anggota').html('');
      $.each(result,function(i,value){
      $('#list-anggota').append('<div class="list-anggota" onclick="pilih_anggota('+value.id+')">'+
        '<div class="media">'+
          '<div class="avatar-thumbnail avatar-sm rounded-circle mr-2">'+
            '<img style="margin-right:10px;" src="'+value.avatar+'" alt="" style="max-width:none" class="rounded-circle">'+
          '</div>'+
          '<div class="media-body align-self-center" >'+
            '<p class="text-muted mb-0">No. '+value.no_anggota+'</p>'+
            '<h5 class="text-truncate font-size-16">'+value.nama_lengkap+'</h5>'+
          '</div>'+
        '</div>'+
      '</div>');
      });
      $('#loading').hide();
      $('#list-anggota').show();
    });
  };

  function pilih_anggota(id){
    if(id=='show'){
      search_anggota();
      $('#modal-anggota').modal('show');
    }
    else{
      $.get("{{ url('api/find_anggota') }}/"+id,function(result){
        $('#anggota_id').val(id);
        $('#nama_lengkap').html(result.nama_lengkap);
        $('#no_anggota').html(result.no_anggota);
        $('#fid_anggota').val(result.no_anggota);
        $('#angsuran_belanja_toko').html('Rp '+accounting.formatNumber(result.angsuran_belanja_toko,0,'.',','));
        $('#angsuran_belanja_konsinyasi').html('Rp '+accounting.formatNumber(result.angsuran_belanja_konsinyasi,0,'.',','));
        $('#angsuran_belanja_online').html('Rp '+accounting.formatNumber(result.angsuran_belanja_online,0,'.',','));
        total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_konsinyasi+result.angsuran_belanja_online;
        $('#total_angsuran_belanja').html('Rp '+accounting.formatNumber(total_angsuran_belanja,0,'.',','));
        $('#angsuran_simpanan').html('Rp 350.000');
        total_angsuran_berkala=result.setoran_berkala;
        $('#setoran_berkala').html('Rp '+accounting.formatNumber(total_angsuran_berkala,0,'.',','));
        total_angsuran_pinjaman=result.angsuran_jangka_pendek+result.angsuran_jangka_panjang+result.angsuran_barang;
        $('#angsuran_pinjaman').html('Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',','));
        total_angsuran=total_angsuran_belanja+total_angsuran_berkala+total_angsuran_pinjaman+350000;
        $('#total_angsuran').html('Rp '+accounting.formatNumber(total_angsuran,0,'.',','));
        $('#fid_anggota').val(result.no_anggota);
        $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
        $('#bulan').val(result.bulan);
        $('#bulan_gaji').html('bulan '+result.bulan_tampil);
        calc_belanja();
        $('#modal-anggota').modal('hide');
      });
    }
  }
  </script>
@endsection
