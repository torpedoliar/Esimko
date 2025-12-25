@php
  $app='sinjam';
  $page='Setoran Simpanan';
  $subpage='Setoran Langsung';
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
        <img src="{{asset('assets/images/icon-page/wallet.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Setoran Simpanan</h4>
          <p class="text-muted m-0">Formulir setoran data simpanan sukarela yang dilakukan oleh petugas</p>
        </div>
      </div>
    </div>
    <form action="{{url('simpanan/sukarela/proses')}}" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="card">
        <div class="card-header">
          @if(!empty($data['simpanan']) && $data['simpanan']->fid_status == 2 )
            <h5>Ajukan Ulang Setoran Simpanan Sukarela</h5>
          @else
            <h5>{{($action=='add' ? 'Tambah' : 'Edit')}} Setoran Simpanan Sukarela</h5>
          @endif
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <div style="border:#dfe4e9 dashed 2px ;padding:20px">
                <h5 class="mb-3"># Identitas Anggota</h5>
                <div class="row">
{{--                  <div class="col-auto">--}}
{{--                    <div class="avatar-wrapper" id="avatar" style="height:100px;width:100px">--}}
{{--                      <img src="{{(!empty($data['simpanan']->avatar) ? asset('storage/'.$data['simpanan']->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" />--}}
{{--                    </div>--}}
{{--                  </div>--}}
                  <div class="col">
                    <div class="list-content">
                      <span>No. Anggota</span>
                      <div id="no_anggota" class="info-content">{!!(!empty($data['simpanan']) ? $data['simpanan']->no_anggota :'<hr>')!!}</div>
                    </div>
                    <div class="list-content">
                      <span>Nama Lengkap</span>
                      <div id="nama_lengkap" class="info-content">{!!(!empty($data['simpanan']) ? $data['simpanan']->nama_lengkap :'<hr>')!!}</div>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="no_anggota" value="{{(!empty($data['simpanan']) ? $data['simpanan']->no_anggota : '')}}" id="fid_anggota">
                <button type="button" onclick="pilih_anggota('show')" class="btn btn-secondary btn-block mt-3">PILIH ANGGOTA</button>
              </div>
            </div>
            <div class="col-md-9">
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Tanggal</label>
                    <input type="text" name="tanggal" autocomplete="off" value="{{(!empty($data['simpanan']) ? \App\Helpers\GlobalHelper::dateFormat($data['simpanan']->tanggal,'d-m-Y') : date('d-m-Y'))}}" class="datepicker form-control">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Jenis Simpanan</label>
                    <select name="jenis_simpanan" class="form-control select2">
                      @foreach ($data['jenis_simpanan'] as $key => $value)
                        <option value="{{$value->id}}" {{(!empty($data['simpanan']) && $data['simpanan']->fid_jenis_transaksi==$value->id ? 'selected' : '')}} >{{$value->jenis_transaksi}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Metode Transaksi</label>
                    <select name="metode_transaksi" class="form-control select2">
                      @foreach ($data['metode-transaksi'] as $key => $value)
                        <option value="{{$value->id}}" {{(!empty($data['simpanan']) && $data['simpanan']->fid_metode_transaksi==$value->id ? 'selected' : '')}} >{{$value->keterangan}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Jumlah Simpanan</label>
                    <input type="text" style="text-align:right" class="form-control autonumeric" data-a-dec="." data-a-sep="," name="nominal" value="{{(!empty($data['simpanan']) ? $data['simpanan']->nominal : '0')}}"  >
                    <div style="text-align:right;font-style:italic;color: #a7a7a7;margin-top:5px;font-size:12px">
                      Sisa Saldo : Rp <span id="sisa_saldo"></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group " style="display: none;">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" style="height:125px">-</textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <input type="hidden" name="id" value="{{$id}}">
          <div class="pull-right">
            <a class="btn btn-secondary" href="{{url('simpanan/sukarela')}}" >Kembali</a>
            @if(!empty($data['simpanan']) && $data['simpanan']->fid_status == 2 )
              <input type="hidden" name="action" value="proses_ulang">
              <button class="btn btn-primary" type="submit">Ajukan Ulang</button>
            @else
              <input type="hidden" name="action" value="{{$action}}">
              <button class="btn btn-primary" type="submit">{{($action=='add' ? 'Tambah' : 'Simpan')}}</button>
            @endif
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
          $('#fid_anggota').val(result.no_anggota);
          $('#avatar').html('<img src="'+result.avatar+'" alt="" >');
          $('#modal-anggota').modal('hide');
          $('#sisa_saldo').html(add_commas(result.saldo));
        });
      }
    }
  </script>
@endsection
