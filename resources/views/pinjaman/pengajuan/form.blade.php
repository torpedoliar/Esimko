@php
  $app='sinjam';
  $page='Pengajuan Pinjaman';
  $subpage='Pengajuan Pinjaman';
  $tenor=array('9'=>50,'10'=>18,'11'=>18);
@endphp
@extends('layouts.admin')
@section('title')
  Pengajuan Pinjaman |
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
        <img src="{{asset('assets/images/icon-page/save-money.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Pengajuan Pinjaman</h4>
          <p class="text-muted m-0">Formulir pengajuan pinjaman anggota yang dilakukan oleh petugas</p>
        </div>
      </div>
    </div>
    <form action="{{url('pinjaman/pengajuan/proses')}}" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="card">
        <div class="card-header">
          <h5>Formulir {{$data['jenis-pinjaman']->jenis_transaksi}}</h5>
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
                      <div id="no_anggota" class="info-content">{!!(!empty($data['pinjaman']) ? $data['pinjaman']->no_anggota :'<hr>')!!}</div>
                    </div>
                    <div class="list-content">
                      <span>Nama Lengkap</span>
                      <div id="nama_lengkap" class="info-content">{!!(!empty($data['pinjaman']) ? $data['pinjaman']->nama_lengkap :'<hr>')!!}</div>
                    </div>
                  </div>
                </div>
                <h5 class="mt-4 mb-3">#Angsuran Bulanan</h5>
                <div class="list-content">
                  <span>Angsuran Simpanan</span>
                  <div id="angsuran_simpanan" class="info-content"><hr></div>
                </div>
                <div class="list-content">
                  <span>Angsuran Pinjaman Jangka Panjang</span>
                  <div id="angsuran_pinjaman_jangka_panjang" class="info-content"><hr></div>
                </div>
                <div class="list-content">
                  <span>Angsuran Pinjaman Jangka Pendek</span>
                  <div id="angsuran_pinjaman_jangka_pendek" class="info-content"><hr></div>
                </div>
                <div class="list-content">
                  <span>Angsuran Pinjaman Barang</span>
                  <div id="angsuran_pinjaman_barang" class="info-content"><hr></div>
                </div><div class="list-content">
                  <span>Angsuran Setoran Berkala</span>
                  <div id="angsuran_setoran_berkala" class="info-content"><hr></div>
                </div>
                <div class="list-content">
                  <span>Angsuran Belanja</span>
                  <div id="angsuran_belanja" class="info-content"><hr></div>
                </div>
                <div class="list-content">
                  <span>Total Angsuran</span>
                  <div id="total_angsuran" class="info-content"><hr></div>
                </div>
                <input type="hidden" name="no_anggota" value="{{(!empty($data['pinjaman']) ? $data['pinjaman']->no_anggota : '')}}" id="fid_anggota">
                <input type="hidden" id="anggota_id" value="0">
                <button type="button" onclick="pilih_anggota('show')" class="btn btn-secondary btn-block mt-4">PILIH ANGGOTA</button>
              </div>
            </div>
            <div class="col-md-7">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Tanggal</label>
                    <input type="text" id="tanggal" name="tanggal" autocomplete="off" value="{{(!empty($data['pinjaman']) ? \App\Helpers\GlobalHelper::dateFormat($data['pinjaman']->tanggal,'d-m-Y') : date('d-m-Y') )}}" class="datepicker form-control">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Metode Transaksi</label>
                    <select name="metode_transaksi" class="form-control select2">
                      @foreach ($data['metode-transaksi'] as $key => $value)
                        <option value="{{$value->id}}" {{(!empty($data['pinjaman']) && $data['pinjaman']->fid_metode_transaksi==$value->id ? 'selected' : '')}} >{{$value->keterangan}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Jumlah Pinjaman</label>
                    <input type="text" onkeyup="calc_angsuran()" id="jumlah_pinjaman" style="text-align:right" class="form-control autonumeric" data-a-dec="," data-a-sep="."  name="nominal" value="{{(!empty($data['pinjaman']) ? str_replace('-','',$data['pinjaman']->nominal) : '0')}}"  >
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="control-label">Tenor</label>
                    <input data-toggle="touchspin" onchange="calc_angsuran()" id="tenor" name="tenor" value="{{(!empty($data['pinjaman']) ? $data['pinjaman']->tenor : '1')}}" type="text" value="0" data-max="60">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Angsuran Pokok</label>
                    <input type="text" id="angsuran_pokok" autocomplete="off" style="text-align:right" class="form-control">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Bunga Pinjaman</label>
                    <input type="text" id="bunga_pinjaman" autocomplete="off" style="text-align:right" class="form-control" value="1" readonly>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Total Angsuran</label>
                    <input type="text" id="total_angsuran_pinjaman" autocomplete="off" style="text-align:right" class="form-control">
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
                <textarea name="keterangan" class="form-control" style="height:150px">{{(!empty($data['pinjaman']) ? $data['pinjaman']->keterangan : '')}}</textarea>
              </div>
              <div id="alert"></div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <input type="hidden" name="bulan" id="bulan">
          <input type="hidden" name="id" value="{{$id}}">
          <input type="hidden" name="jenis" value="{{$data['jenis-pinjaman']->id}}">
          <div class="pull-right">
            <a class="btn btn-secondary" href="{{url('pinjaman/pengajuan')}}" >Kembali</a>
            <button class="btn btn-primary" type="submit" name="action" id="action" value="{{$action}}">Simpan</button>
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
      if(anggota_id != 0 ){
        $.get("{{ url('api/find_anggota') }}/"+anggota_id+"/"+bulan_sekarang,function(result){
          $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
          $('#bulan').val(result.bulan);
          $('#bulan_gaji').html('Bulan '+result.bulan_tampil);
        });
      }
    });

    function calc_angsuran(){
      pinjaman = $('#jumlah_pinjaman').val();
      pinjaman = pinjaman.split('.').join('');

      tenor = $('#tenor').val();
      tenor = tenor.split('.').join('');

      bunga = pinjaman * (0.8 / 100);
      angsuran = (pinjaman/tenor);
      angsuran_pinjaman=angsuran+bunga;
      $('#angsuran_pokok').val(accounting.formatNumber(angsuran,0,'.',','));
      $('#bunga_pinjaman').val(accounting.formatNumber(bunga,0,'.',','));
      $('#total_angsuran_pinjaman').val(accounting.formatNumber(angsuran_pinjaman,0,'.',','));

      anggota_id = $('#anggota_id').val();
      tanggal=$('#tanggal').val();
      bulan_sekarang=tanggal.substr(-7);
      if(anggota_id != 0 ){
        $.get("{{ url('api/find_anggota') }}/"+anggota_id+"/"+bulan_sekarang,function(result){

          gaji_pokok =$('#gaji_pokok').val();
          gaji_pokok = gaji_pokok.split('.').join('');

          total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_online+result.angsuran_belanja_konsinyasi;
          total_angsuran_pinjaman=result.angsuran_jangka_panjang+result.angsuran_jangka_pendek+result.angsuran_barang+angsuran_pinjaman+350000;

          total_angsuran=total_angsuran_belanja+total_angsuran_pinjaman+result.setoran_berkala;

          $.get("{{ url('api/check_sisa_pinjaman') }}/"+result.no_anggota+"/{{$data['jenis-pinjaman']->id}}",function (result2){
            if(result2.sisa_tenor == 0 ){
              if(total_angsuran <= gaji_pokok ){
                if(total_angsuran_pinjaman > gaji_pokok/2){
                  color='danger';
                  note='Maaf '+result.nama_lengkap+' belum bisa mengajukan pinjaman dengan total angsuran perbulan Rp '+accounting.formatNumber(angsuran_pinjaman,0,'.',',')+' karena melebihi 50% Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai';
                }
                else{
                  color='success';
                  note='Selamat '+result.nama_lengkap+' bisa melanjutkan pengajuan pinjaman dengan total angsuran perbulan Rp '+accounting.formatNumber(angsuran_pinjaman,0,'.',',')+' Silahkan lanjutkan proses pengajuan sampai pengajuan disetujui oleh petugas';
                }
                $("#action").prop('disabled', false);
              }
              else{
                color='danger';
                note='Maaf '+result.nama_lengkap+' belum bisa mengajukan pinjaman dengan total angsuran perbulan Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',',')+' karena total angsuran melebihi Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai atau ubah kembali nominal setoran berkala';
                $("#action").prop('disabled', true);
              }
            }
            else{
              // color='danger';
              // note='Maaf anda belum bisa mengajukan '+result2.jenis_pinjaman+', karena anda masih mempunyai sisa angsuran '+result2.jenis_pinjaman+' senilai <b>Rp '+accounting.formatNumber(result2.sisa_angsuran,0,'.',',')+'</b> dan masih tersisa <b>'+result2.sisa_tenor+'x angsuran</b>. Silahkan melunasi pinjaman anda atau melakukan pengajuan pinjaman yang lain.';
              // $("#action").prop('disabled', true);
            }
            alert='<div class="alert alert-'+color+'" role="alert">'+note+'</div>';
            $('#alert').html(alert);
          });
        });
      }
    }
    calc_angsuran();

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

    @if(!empty($data['pinjaman']))
    pilih_anggota('{{$data['pinjaman']->anggota_id}}');
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
          total_angsuran_berkala=result.setoran_berkala;
          $('#angsuran_setoran_berkala').html('Rp '+accounting.formatNumber(total_angsuran_berkala,0,'.',','));
          $('#angsuran_pinjaman_jangka_panjang').html('Rp '+accounting.formatNumber(result.angsuran_jangka_panjang,0,'.',','));
          $('#angsuran_pinjaman_jangka_pendek').html('Rp '+accounting.formatNumber(result.angsuran_jangka_pendek,0,'.',','));
          $('#angsuran_pinjaman_barang').html('Rp '+accounting.formatNumber(result.angsuran_barang,0,'.',','));
          total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_online+result.angsuran_belanja_konsinyasi;
          $('#angsuran_belanja').html('Rp '+accounting.formatNumber(total_angsuran_belanja,0,'.',','));
          total_angsuran=result.angsuran_jangka_panjang+result.angsuran_jangka_pendek+result.angsuran_barang+total_angsuran_berkala+350000;
          $('#total_angsuran').html('Rp '+accounting.formatNumber(total_angsuran,0,'.',','));
          $('#fid_anggota').val(result.no_anggota);
          $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
          $('#bulan').val(result.bulan);
          $('#bulan_gaji').html('bulan '+result.bulan_tampil);
          calc_angsuran();
          $('#modal-anggota').modal('hide');
        });
      }
    }
  </script>
@endsection
