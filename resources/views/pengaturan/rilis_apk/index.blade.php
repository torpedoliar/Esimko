@php
  $app='master';
  $page='Pengaturan';
  $subpage='Rilis Aplikasi';
@endphp
@extends('layouts.admin')
@section('title')
  Rilis Aplikasi |
@endsection
@section('css')
  <style>
    .sha-cell { font-family: monospace; font-size: 11px; word-break: break-all; }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <i class="fa fa-android avatar-md mr-3 mt-1" style="font-size:36px;color:#118334"></i>
      <div class="media-body">
        <h4 class="mt-0 mb-1">Rilis Aplikasi Mobile</h4>
        <p class="text-muted mb-0">Upload APK baru — otomatis jadi versi terbaru, menulis <code>version.json</code>, dan tersedia di halaman publik <a href="{{url('apk')}}" target="_blank">{{url('apk')}}</a></p>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-5">
      <div class="card">
        <div class="card-body">
          <h4 class="header-title mb-3">Upload Rilis Baru</h4>
          <form action="{{url('pengaturan/rilis_apk/proses')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
              <label>Versi <span class="text-danger">*</span></label>
              <input type="text" name="versi" class="form-control" placeholder="contoh: 1.0.3" required>
            </div>
            <div class="form-group">
              <label>Version Code <span class="text-danger">*</span></label>
              <input type="number" name="version_code" class="form-control" placeholder="harus lebih besar dari rilis terbaru" required>
              @php $tb = $data['terbaru'] ?? null; @endphp
              @if(!empty($tb))
                <small class="text-muted">Terbaru saat ini: v{{$tb->versi}} (code {{$tb->version_code}})</small>
              @endif
            </div>
            <div class="form-group">
              <label>Changelog</label>
              <textarea name="changelog" class="form-control" rows="3" placeholder="Apa yang baru di versi ini (tampil di halaman unduh)"></textarea>
            </div>
            <div class="form-group">
              <label>File APK <span class="text-danger">*</span></label>
              <input type="file" name="apk" class="form-control" accept=".apk" required>
              <small class="text-muted">Maksimal 60 MB</small>
            </div>
            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="wajib_update" value="1" class="custom-control-input" id="wajibUpdate">
                <label class="custom-control-label" for="wajibUpdate">Wajib update (versi lama dipaksa memperbarui)</label>
              </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan Rilis</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card">
        <div class="card-body">
          <h4 class="header-title mb-3">Riwayat Rilis</h4>
          <div class="table-responsive">
            <table class="table table-centered mb-0">
              <thead>
                <tr>
                  <th>Versi</th>
                  <th>Tanggal</th>
                  <th>Ukuran</th>
                  <th>Unduhan</th>
                  <th>SHA-256</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach($data['rilis'] as $r)
                <tr>
                  <td>
                    <strong>v{{$r->versi}}</strong> <small class="text-muted">(code {{$r->version_code}})</small>
                    @if(!empty($data['terbaru']) && $r->id == $data['terbaru']->id)
                      <span class="badge badge-success ml-1">Terbaru</span>
                    @endif
                    @if(!empty($r->changelog))
                      <div><small class="text-muted">{{$r->changelog}}</small></div>
                    @endif
                  </td>
                  <td><small>{{date('d M Y', strtotime($r->created_at))}}</small></td>
                  <td><small>{{number_format($r->ukuran / 1048576, 1)}} MB</small></td>
                  <td>{{$r->unduhan}}</td>
                  <td class="sha-cell">{{substr($r->sha256, 0, 16)}}…</td>
                  <td class="text-right">
                    <a href="{{url('apk/unduh?id='.$r->id)}}" class="btn btn-sm btn-outline-primary"><i class="fa fa-download"></i></a>
                    <a href="{{url('pengaturan/rilis_apk/hapus?id='.$r->id)}}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus rilis v{{$r->versi}}? File APK ikut terhapus.')"><i class="fa fa-trash"></i></a>
                  </td>
                </tr>
                @endforeach
                @if(count($data['rilis']) == 0)
                <tr><td colspan="6" class="text-center text-muted">Belum ada rilis — upload APK pertama di form sebelah kiri.</td></tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
