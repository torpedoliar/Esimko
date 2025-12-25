@php
  $app='sinjam';
  $page='Penarikan Simpanan';
  $subpage='Semua Simpanan';
@endphp
@extends('layouts.admin')
@section('title')
  Semua Simpanan |
@endsection
@section('css')
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
      <img src="{{asset('assets/images/icon-page/penarikan.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Penarikan Semua Simpanan</h4>
        <p class="text-muted m-0">Formulir pengisian data penarikan semua simpanan yang dilakukan oleh petugas</p>
      </div>
    </div>
  </div>
  <form action="{{url('penarikan/penutupan/proses')}}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="card">
      <div class="card-header">
        <h5>Formulir Penarikan Semua Simpanan</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div style="border:#dfe4e9 dashed 2px ;padding:20px">
              <h5 class="mb-3">Identitas Anggota</h5>
              <div class="row">
                <div class="col-auto">
                  <div class="avatar-wrapper" id="avatar" style="height:100px;width:100px">
                    <img src="{{(!empty($data['penarikan']->avatar) ? asset('storage/'.$data['penarikan']->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}">
                  </div>
                </div>
                <div class="col">
                  <div class="list-content">
                    <span>No. Anggota</span>
                    <div id="no_anggota" class="info-content">{!!(!empty($data['penarikan']) ? $data['penarikan']->no_anggota :'<hr>')!!}</div>
                  </div>
                  <div class="list-content">
                    <span>Nama Lengkap</span>
                    <div id="nama_lengkap" class="info-content">{!!(!empty($data['penarikan']) ? $data['penarikan']->nama_lengkap :'<hr>')!!}</div>
                  </div>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-md-6">
                  <h5 class="mb-3">Saldo Simpanan</h5>
                  <div class="list-content">
                    <span>Simpanan Wajib</span>
                    <div id="simpanan_wajib" class="info-content"><hr></div>
                  </div>
                  <div class="list-content">
                    <span>Simpanan Pokok</span>
                    <div id="simpanan_pokok" class="info-content"><hr></div>
                  </div>
                  <div class="list-content">
                    <span>Simpanan Hari Raya</span>
                    <div id="simpanan_hari_raya" class="info-content"><hr></div>
                  </div>
                  <div class="list-content">
                    <span>Simpanan Sukarela</span>
                    <div id="simpanan_sukarela" class="info-content"><hr></div>
                  </div>
                  <div class="list-content">
                    <span>Total Simpanan</span>
                    <div id="total_simpanan" class="info-content"><hr></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <h5 class="mb-3">Total Tagihan</h5>
                  <div class="list-content">
                    <span>Sisa Pinjaman</span>
                    <div id="sisa_pinjaman" class="info-content"><hr></div>
                  </div>
                  <div class="list-content">
                    <span>Bunga Pinjaman (1%)</span>
                    <div id="bunga_pinjaman" class="info-content"><hr></div>
                  </div>
                  <div class="list-content">
                    <span>Sisa Kredit Belanja</span>
                    <div id="sisa_kredit_belanja" class="info-content"><hr></div>
                  </div>
                  <div class="list-content">
                    <span>Total Tagihan</span>
                    <div id="total_tagihan" class="info-content"><hr></div>
                  </div>
                </div>
              </div>
              <input type="hidden" name="no_anggota" value="{{(!empty($data['penarikan']) ? $data['penarikan']->no_anggota : '')}}" id="fid_anggota">
              <button type="button" onclick="pilih_anggota('show')" class="btn btn-secondary btn-block mt-2">PILIH ANGGOTA</button>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row">
              <div class="col-md-5">
                <div class="form-group">
                  <label>Tanggal</label>
                  <input type="text" name="tanggal" autocomplete="off" class="datepicker form-control" value="{{(!empty($data['penarikan']) ? \App\Helpers\GlobalHelper::dateFormat($data['penarikan']->tanggal,'d-m-Y') : date('d-m-Y'))}}">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-5">
                <div class="form-group">
                  <label>Metode Penarikan</label>
                  <select name="metode_transaksi" class="form-control select2">
                    @foreach ($data['metode-transaksi'] as $key => $value)
                    <option value="{{$value->id}}" {{(!empty($data['penarikan']) && $data['penarikan']->fid_metode_transaksi==$value->id ? 'selected' : '')}} >{{$value->keterangan}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-7">
                <div class="form-group">
                  <label>Jumlah Penarikan (Rp)</label>
                  <input type="text" name="jumlah" id="nominal" style="text-align:right" class="form-control autonumeric" data-a-dec="." data-a-sep="," value="{{(!empty($data['penarikan']) ? str_replace('-','',$data['penarikan']->nominal) : 0 )}}" readonly >
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="keterangan" class="form-control" style="height:110px">{{(!empty($data['penarikan']) ? $data['penarikan']->keterangan : '' )}}</textarea>
            </div>
            <div class="alert" id="alert" role="alert">
              <p></p>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <input type="hidden" name="action" value="{{$action}}">
        <input type="hidden" name="id" value="{{$id}}">
        <div class="pull-right">
          <a class="btn btn-secondary" href="{{url('penarikan/penutupan')}}" >Kembali</a>
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
            <button class="btn btn-dark" onclick="search_anggota()">Search</button>
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
<script src="{{asset('assets/js/accounting.js')}}"></script>
<script>
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
      $('#nama_lengkap').html(result.nama_lengkap);
      $('#no_anggota').html(result.no_anggota);
      $('#simpanan_wajib').html('Rp '+accounting.formatNumber(result.simpanan_wajib,0,'.',','));
      $('#simpanan_pokok').html('Rp '+accounting.formatNumber(result.simpanan_pokok,0,'.',','));
      $('#simpanan_hari_raya').html('Rp '+accounting.formatNumber(result.simpanan_hari_raya,0,'.',','));
      $('#simpanan_sukarela').html('Rp '+accounting.formatNumber(result.saldo,0,'.',','));
      $('#total_simpanan').html('Rp '+accounting.formatNumber(result.total_simpanan,0,'.',','));

      $('#sisa_pinjaman').html('Rp '+accounting.formatNumber(result.sisa_pinjaman,0,'.',','));
      $('#sisa_kredit_belanja').html('Rp '+accounting.formatNumber(result.sisa_kredit_belanja,0,'.',','));
      $('#bunga_pinjaman').html('Rp '+accounting.formatNumber(result.bunga_pinjaman,0,'.',','));
      total_kredit_belanja=result.sisa_pinjaman+result.sisa_kredit_belanja+result.bunga_pinjaman;
      $('#total_tagihan').html('Rp '+accounting.formatNumber(total_kredit_belanja,0,'.',','));

      $('#nominal').val(accounting.formatNumber(result.sisa_saldo,0,'.',','));

      $('#avatar').html('<img src="'+result.avatar+'" alt="" >');

      if(result.sisa_saldo <= 0 ){
        $('#action').prop('disabled', true);
        $('#alert').show();
        if(result.sisa_saldo == 0 ){
          $('#alert').addClass('alert-warning');
          $('#alert p').html('Maaf Sisa saldo anda Rp 0,-, anda tidak bisa melakukan penarikan semua simpanan');
        }
        else{
          $('#alert').addClass('alert-danger');
          $('#alert p').html('Maaf anda masih mempunya tagihan sebesar Rp '+accounting.formatNumber(result.sisa_saldo,0,'.',',')+', anda tidak bisa melakukan penarikan semua simpanan');
        }
      }
      else{
        $('#action').prop('disabled', false);
        $('#alert').hide();
      }
      $('#fid_anggota').val(result.no_anggota);
      $('#modal-anggota').modal('hide');
    });
  }
}
</script>
@endsection
