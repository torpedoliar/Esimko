@php
  $app='master';
  $page='Data Master';
  $subpage='Otoritas User';
@endphp
@extends('layouts.admin')
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0 font-size-18">Otoritas User</h4>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-md-4">
          <form action="" method="get">
            <select id="hak_akses"  class="select2" name="hak_akses" style="width:100%" onchange="javascript:submit()">
              @foreach ($data['hak-akses'] as $key => $value)
              <option value="{{$value->id}}" {{($value->id==$hak_akses ? 'selected' : '')}}>{{$value->hak_akses}}</option>
              @endforeach
            </select>
          </form>
        </div>
      </div>
    </div>
    <div class="card-body">
      <form action="{{ url('master/otoritas_user/proses') }}" method="POST">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th width="30px">No</th>
              <th colspan="2">Nama Modul</th>
              <th style="width:80px;text-align:center;line-height:25px">View<br><input type="checkbox" id="check_all_view"></th>
              <th style="width:80px;text-align:center;line-height:25px">Insert<br><input type="checkbox" id="check_all_insert"></th>
              <th style="width:80px;text-align:center;line-height:25px">Update<br><input type="checkbox" id="check_all_update"></th>
              <th style="width:80px;text-align:center;line-height:25px">Delete<br><input type="checkbox" id="check_all_delete"></th>
              <th style="width:80px;text-align:center;line-height:25px">All User<br><input type="checkbox" id="check_all_all_user"></th>
              <th style="width:80px;text-align:center;line-height:25px">Print<br><input type="checkbox" id="check_all_print"></th>
              <th style="width:80px;text-align:center;line-height:25px">Verified<br><input type="checkbox" id="check_all_verified"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data['modul'] as $key => $value)
              @php($otoritas=$value->otoritas)
              <tr style="background:whitesmoke">
                <th style="text-align:left">{{$key+1}}</th>
                <th colspan="2">{{$value->nama_modul}}<input type="hidden" name="id[]" value="{{$value->id}}" ></th>
                <th style="text-align:center"><input type="checkbox" @if($otoritas['view'] == 'Y') checked @endif class="checkbox_view" name="view[{{ $value->id }}]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" @if($otoritas['insert'] == 'Y') checked @endif class="checkbox_insert" name="insert[{{ $value->id }}]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" @if($otoritas['update'] == 'Y') checked @endif class="checkbox_update" name="update[{{ $value->id }}]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" @if($otoritas['delete'] == 'Y') checked @endif class="checkbox_delete" name="delete[{{ $value->id }}]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" @if($otoritas['all_user'] == 'Y') checked @endif class="checkbox_all_user" name="all_user[{{ $value->id }}]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" @if($otoritas['print'] == 'Y') checked @endif class="checkbox_print" name="print[{{ $value->id }}]" value="Y" ></th>
                <th style="text-align:center"><input type="checkbox" @if($otoritas['verified'] == 'Y') checked @endif class="checkbox_verified" name="verified[{{ $value->id }}]" value="Y" ></th>
              </tr>
              @foreach ($value->submodul as $key2 => $value2)
                @php($otoritas=$value2->otoritas)
                <tr>
                  <td style="text-align:left">{{$key+1}}.{{$key2+1}}</td>
                  <td colspan="2">{{$value2->nama_modul}}<input type="hidden" name="id[]" value="{{$value2->id}}" ></td>
                  <th style="text-align:center"><input type="checkbox" @if($otoritas['view'] == 'Y') checked @endif class="checkbox_view" name="view[{{ $value2->id }}]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" @if($otoritas['insert'] == 'Y') checked @endif class="checkbox_insert" name="insert[{{ $value2->id }}]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" @if($otoritas['update'] == 'Y') checked @endif class="checkbox_update" name="update[{{ $value2->id }}]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" @if($otoritas['delete'] == 'Y') checked @endif class="checkbox_delete" name="delete[{{ $value2->id }}]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" @if($otoritas['all_user'] == 'Y') checked @endif class="checkbox_all_user" name="all_user[{{ $value2->id }}]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" @if($otoritas['print'] == 'Y') checked @endif class="checkbox_print" name="print[{{ $value2->id }}]" value="Y" ></th>
                  <th style="text-align:center"><input type="checkbox" @if($otoritas['verified'] == 'Y') checked @endif class="checkbox_verified" name="verified[{{ $value2->id }}]" value="Y" ></th>
                </tr>
                @foreach ($value2->submodul as $key3 => $value3)
                  @php($otoritas=$value3->otoritas)
                  <tr>
                    <td></td>
                    <td style="text-align:left;width:1px;white-space:nowrap">{{$key+1}}.{{$key2+1}}.{{$key3+1}}</td>
                    <td>{{$value3->nama_modul}}<input type="hidden" name="id[]" value="{{$value3->id}}" ></td>
                    <th style="text-align:center"><input type="checkbox" @if($otoritas['view'] == 'Y') checked @endif class="checkbox_view" name="view[{{ $value3->id }}]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" @if($otoritas['insert'] == 'Y') checked @endif class="checkbox_insert" name="insert[{{ $value3->id }}]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" @if($otoritas['update'] == 'Y') checked @endif class="checkbox_update" name="update[{{ $value3->id }}]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" @if($otoritas['delete'] == 'Y') checked @endif class="checkbox_delete" name="delete[{{ $value3->id }}]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" @if($otoritas['all_user'] == 'Y') checked @endif class="checkbox_all_user" name="all_user[{{ $value3->id }}]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" @if($otoritas['print'] == 'Y') checked @endif class="checkbox_print" name="print[{{ $value3->id }}]" value="Y" ></th>
                    <th style="text-align:center"><input type="checkbox" @if($otoritas['verified'] == 'Y') checked @endif class="checkbox_verified" name="verified[{{ $value3->id }}]" value="Y" ></th>
                  </tr>
                @endforeach
              @endforeach
            @endforeach
          </tbody>
        </table>
        {{ csrf_field() }}
        <input type="hidden" name="hak_akses" value="{{$hak_akses}}">
  			<button type="submit" class="btn btn-block btn-primary" >SIMPAN</button>
		  </form>
    </div>
  </div>
</div>
@endsection
@section('js')
  <script>
    $("#check_all_view").click(function(){
      $('.checkbox_view').not(this).prop('checked', this.checked);
    });
    $("#check_all_insert").click(function(){
      $('.checkbox_insert').not(this).prop('checked', this.checked);
    });
    $("#check_all_update").click(function(){
      $('.checkbox_update').not(this).prop('checked', this.checked);
    });
    $("#check_all_delete").click(function(){
      $('.checkbox_delete').not(this).prop('checked', this.checked);
    });
    $("#check_all_all_user").click(function(){
      $('.checkbox_all_user').not(this).prop('checked', this.checked);
    });
    $("#check_all_print").click(function(){
      $('.checkbox_print').not(this).prop('checked', this.checked);
    });
    $("#check_all_verified").click(function(){
      $('.checkbox_verified').not(this).prop('checked', this.checked);
    });
  </script>
@endsection
