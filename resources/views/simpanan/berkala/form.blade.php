@php
  $app='sinjam';
  $page='Setoran Simpanan';
  $subpage='Setoran Berkala';
@endphp
@extends('layouts.admin')
@section('title')
  Setoran Simpanan |
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
      <img src="{{asset('assets/images/icon-page/calendar.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Setoran Simpanan Berkala</h4>
        <p class="text-muted m-0">Formulir setoran berkala simpanan sukarela yang dilakukan oleh petugas</p>
      </div>
    </div>
  </div>
  <form action="{{url('simpanan/sukarela/berkala/proses')}}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="card">
      <div class="card-header">
        <h5>{{($action=='add' ? 'Tambah' : 'Edit')}} Setoran Berkala Simpanan Sukarela</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-5">
            <div style="border:#dfe4e9 dashed 2px ;padding:20px">
              <h5 class="mb-3"># Identitas Anggota</h5>
              <div class="row">
                <div class="col-auto">
                  <div class="avatar-wrapper" id="avatar" style="height:100px;width:100px">
                    <img src="{{(!empty($data['setoran-berkala']->avatar) ? asset('storage/'.$data['setoran-berkala']->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" />
                  </div>
                </div>
                <div class="col">
                  <div class="list-content">
                    <span>No. Anggota</span>
                    <div id="no_anggota" class="info-content">{!!(!empty($data['setoran-berkala']) ? $data['setoran-berkala']->no_anggota :'<hr>')!!}</div>
                  </div>
                  <div class="list-content">
                    <span>Nama Lengkap</span>
                    <div id="nama_lengkap" class="info-content">{!!(!empty($data['setoran-berkala']) ? $data['setoran-berkala']->nama_lengkap :'<hr>')!!}</div>
                  </div>
                </div>
              </div>
              <h5 class="mt-4 mb-3">#Angsuran Bulanan</h5>
              <div class="list-content">
                <span>Angsuran Simpanan</span>
                <div id="angsuran_simpanan" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Angsuran Pinjaman</span>
                <div id="angsuran_pinjaman" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Angsuran Belanja</span>
                <div id="angsuran_belanja" class="info-content"><hr></div>
              </div>
              <div class="list-content">
                <span>Total Angsuran</span>
                <div id="total_angsuran" class="info-content"><hr></div>
              </div>
              <input type="hidden" name="no_anggota" value="{{(!empty($data['setoran-berkala']) ? $data['setoran-berkala']->no_anggota : '')}}" id="fid_anggota">
              <input type="hidden" id="anggota_id" value="{{(!empty($data['setoran-berkala']) ? $data['setoran-berkala']->id : '')}}" >
              <button type="button" onclick="pilih_anggota('show')" class="btn btn-secondary btn-block mt-3">PILIH ANGGOTA</button>
            </div>
          </div>
          <div class="col-md-7">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tanggal</label>
                  <input type="text" id="tanggal" onchange="calc_angsuran()" name="tanggal" autocomplete="off" value="{{(!empty($data['setoran-berkala']) ? \App\Helpers\GlobalHelper::dateFormat($data['setoran-berkala']->tanggal,'d-m-Y') : date('d-m-Y') )}}" class="datepicker form-control">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Jumlah Setoran</label>
                  <input type="text" id="jumlah_setoran" onkeyup="calc_angsuran()" style="text-align:right" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="nominal" value="{{(!empty($data['setoran-berkala']) ? $data['setoran-berkala']->nominal : '0')}}"  >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tipe Jadwal Setoran</label>
                  <select class="select2 form-control" id="tipe_jadwal" name="tipe_jadwal">
                    <option value="range_bulan" >Range Bulan</option>
                    <option value="unlimited_range" {{(!empty($data['setoran-berkala']) && $data['setoran-berkala']->bulan_akhir=='Belum Ditentukan' ? 'selected' : '')}}>Unlimited Range</option>
                  </select>
                </div>
              </div>
              <div class="col-md-8">
                <div id="range_bulan" class="form_jadwal">
                  <div class="form-group">
                    <label>Range Bulan</label>
                    <div>
                      <div class="input-daterange input-group" data-provide="datepicker">
                        <input type="text" class="form-control" value="{{(!empty($data['setoran-berkala']) ? $data['setoran-berkala']->bulan_awal : date('m-Y'))}}" autocomplete="off" name="bulan_awal" placeholder="Dari Bulan" />
                        <input type="text" class="form-control" value="{{(!empty($data['setoran-berkala']) ? ($data['setoran-berkala']->bulan_akhir == 'Belum Ditentukan' ? date('m-Y') : $data['setoran-berkala']->bulan_akhir ) : date('m-Y'))}}" autocomplete="off" name="bulan_akhir" placeholder="Sampai Bulan" />
                      </div>
                    </div>
                  </div>
                </div>
                <div id="unlimited_range" class="form_jadwal">
                  <div class="form-group">
                    <label>Setoran Mulai Bulan</label>
                    <input type="text" class="monthpicker form-control" value="{{(!empty($data['setoran-berkala']) ? $data['setoran-berkala']->bulan_awal : date('m-Y'))}}" name="mulai_bulan" autocomplete="off" />
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Gaji Pokok</label>
                  <input type="text" id="gaji_pokok" onchange="calc_angsuran()" style="text-align:right" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="gaji_pokok" >
                  <div id="bulan_gaji" style="text-align:right;margin-top:5px;font-size:12px"></div>
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label class="control-label">Slip Gaji</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFile" name="attachment">
                    <label class="custom-file-label" for="customFile">Choose file</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="keterangan" class="form-control" style="height:70px">{{(!empty($data['setoran-berkala']) ? $data['setoran-berkala']->keterangan : '')}}</textarea>
            </div>
            <div id="alert"></div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <input type="hidden" name="bulan"  id="bulan">
        <input type="hidden" name="id" value="{{$id}}">
        <div class="pull-right">
          <a class="btn btn-secondary" href="{{url('simpanan/sukarela/berkala')}}" >Kembali</a>
          <input type="hidden" name="action" value="{{$action}}">
          <button class="btn btn-primary" type="submit">{{($action=='add' ? 'Tambah' : 'Simpan')}}</button>
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

$(function () {
  $("#tipe_jadwal").trigger('change');
});

$("#tanggal").change(function(){
  anggota_id = $('#anggota_id').val();
  tanggal=$('#tanggal').val();
  bulan_sekarang=tanggal.substr(-7);
  if(anggota_id != 0 ){
    $.get("{{ url('api/find_anggota') }}/"+anggota_id+"/"+bulan_sekarang,function(result){
      $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
      $('#bulan').val(result.bulan);
      $('#bulan_gaji').html('Bulan '+result.bulan_tampil);
    });
  }
});

function calc_angsuran(){
  setoran = $('#jumlah_setoran').val();
  setoran = setoran.split('.').join('');

  gaji_pokok =$('#gaji_pokok').val();
  gaji_pokok = gaji_pokok.split('.').join('');

  anggota_id = $('#anggota_id').val();
  tanggal=$('#tanggal').val();
  bulan_sekarang=tanggal.substr(-7);
  if(anggota_id != 0 ){
    $.get("{{ url('api/find_anggota') }}/"+anggota_id+"/"+bulan_sekarang,function(result){
      total_angsuran_pinjaman=result.angsuran_jangka_panjang+result.angsuran_jangka_pendek+result.angsuran_barang;
      total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_online+result.angsuran_belanja_konsinyasi;
      total_angsuran=total_angsuran_pinjaman+total_angsuran_belanja+350000;
      @if($action=='add')
      if(result.setoran_berkala == 0 ){
        if(total_angsuran > gaji_pokok){
          color='danger';
          note='Maaf '+result.nama_lengkap+' belum bisa mengajukan setoran berkala perbulan Rp '+accounting.formatNumber(total_angsuran,0,'.',',')+' karena total payroll per bulan melebihi 50% Gaji Pokok. Silahkan masukkan jumlah setoran simpanan yang sesuai';
        }
        else{
          color='success';
          note='Selamat '+result.nama_lengkap+' bisa mengajukan setoran berkala perbulan Rp '+accounting.formatNumber(total_angsuran,0,'.',',')+'. Silahkan lanjutkan proses pengajuan sampai dengan disetujui oleh petugas';
        }
      }
      else{
        color='danger';
        note='Maaf '+result.nama_lengkap+' belum bisa mengajukan setoran berkala, karena masih terdapat setoran berkala yang aktif. Silahkan mengubah atau mengnonaktifkan setoran berkala yang lama.';
      }
      @else
      if(total_angsuran > gaji_pokok){
        color='danger';
        note='Maaf '+result.nama_lengkap+' belum bisa mengajukan setoran berkala perbulan Rp '+accounting.formatNumber(total_angsuran,0,'.',',')+' karena total payroll per bulan melebihi 50% Gaji Pokok. Silahkan masukkan jumlah setoran simpanan yang sesuai';
      }
      else{
        color='success';
        note='Selamat '+result.nama_lengkap+' bisa mengajukan setoran berkala perbulan Rp '+accounting.formatNumber(total_angsuran,0,'.',',')+'. Silahkan lanjutkan proses pengajuan sampai dengan disetujui oleh petugas';
      }
      @endif
      alert='<div class="alert alert-'+color+'" role="alert">'+note+'</div>';
      $('#alert').html(alert);
    });
  }
}
calc_angsuran();
@if(!empty($data['setoran-berkala']))
pilih_anggota('{{$data['setoran-berkala']->anggota_id}}');
@endif

function pilih_anggota(id){
  if(id=='show'){
    search_anggota();
    $('#modal-anggota').modal('show');
  }
  else{
    tanggal=$('#tanggal').val();
    bulan_sekarang=tanggal.substr(-7);
    $.get("{{ url('api/find_anggota') }}/"+id+"/"+bulan_sekarang,function(result){
      $('#anggota_id').val(id);
      $('#nama_lengkap').html(result.nama_lengkap);
      $('#no_anggota').html(result.no_anggota);
      $('#avatar').html('<img src="'+result.avatar+'" alt="" >');
      $('#angsuran_simpanan').html('Rp 350.000');
      total_angsuran_pinjaman=result.angsuran_jangka_panjang+result.angsuran_jangka_pendek+result.angsuran_barang;
      $('#angsuran_pinjaman').html('Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',','));
      total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_online+result.angsuran_belanja_konsinyasi;
      $('#angsuran_belanja').html('Rp '+accounting.formatNumber(total_angsuran_belanja,0,'.',','));
      total_angsuran=total_angsuran_pinjaman+total_angsuran_belanja+350000;
      $('#total_angsuran').html('Rp '+accounting.formatNumber(total_angsuran,0,'.',','));
      $('#fid_anggota').val(result.no_anggota);
      $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
      $('#bulan').val(result.bulan);
      $('#bulan_gaji').html('Bulan '+result.bulan_tampil);
      calc_angsuran();
      $('#modal-anggota').modal('hide');
    });
  }
}

$("#tipe_jadwal").change(function(){
  form_jadwal=$(this).val();
  $('.form_jadwal').hide();
  $('#'+form_jadwal).show();
});

$('.input-daterange').datepicker({
  autoclose: true,
  format: "mm-yyyy",
  startView: 1,
  minViewMode: 1
});

</script>
@endsection
