@php
  $app='laporan';
  $page='Jurnal Umum';
  $subpage='Jurnal Umum';
@endphp
@extends('layouts.admin')
@section('title')
  Jurnal Umum |
@endsection
@section('content')
<div class="content-breadcrumb mb-2">
  <div class="container-fluid">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/book.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Jurnal Umum</h4>
          <p class="text-muted m-0">Menampilkan Jurnal Umum dari semua transaksi yang ada di Koperasi</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-9">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="" name="search" placeholder="Cari Data Jurnal">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <button type="button" class="btn btn-primary btn-block" data-target="#form-jurnal" data-toggle="modal" >Tambah</button>
      </div>
    </div>
  </div>
</div>
<div class="container-fluid">
  @if(count($data['jurnal'])==0)
    <div style="width:100%;text-align:center">
      <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
      <h4 class="mt-2">Data Jurnal tidak Ditemukan</h4>
    </div>
  @else
    <table class="table table-middle table-custom">
      <thead class="thead-light">
        <tr>
          <th width="20px">No</th>
          <th class="center">Tanggal</th>
          <th>No. Jurnal</th>
          <th>Deskripsi</th>
          <th style="text-align:right">Debit</th>
          <th style="text-align:right">Kredit</th>
          <th>Created by</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['jurnal'] as $key => $value)
        <tr>
          <td style="border-color:{{($value->st_balance==1 ? '#007434' : '#b10000')}}">{{ $data['jurnal']->firstItem() + $key }}</td>
          <td style="width:1px;white-space:nowrap">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
          <td style="width:1px;white-space:nowrap">{{$value->nomor_jurnal}}</td>
          <td>{{$value->deskripsi}}</td>
          <td style="text-align:right">{{number_format($value->debit,0,'.',',')}}</td>
          <td style="text-align:right">{{number_format($value->kredit,0,'.',',')}}</td>
          <td style="width:1px;white-space:nowrap">
            <h6>{{$value->nama_lengkap}}</h6>
            at {{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}
          </td>
          <td style="width:1px;white-space:nowrap">
            <div class="text-center">
              <a href="{{url('keuangan/jurnal_umum/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
              <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
              <form action="{{url('keuangan/jurnal_umum/proses')}}" method="post" id="hapus{{$value->id}}">
                {{ csrf_field()}}
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="{{$value->id}}">
              </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
<div class="modal fade" id="form-jurnal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title">Tambah Jurnal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{url('keuangan/jurnal_umum/proses')}}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="modal-body">
          <div class="form-group">
            <label>Tanggal</label>
            <input type="text" class="form-control datepicker" autocomplete="off" name="tanggal">
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea class="form-control" name="deskripsi" style="height:100px"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id" value="0">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" name="action" value="add">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
@section('js')
  <script>


  </script>
@endsection
