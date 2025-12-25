@php
    $page='Poin of Sales';
    $subpage='Belanja Konsinyasi';
@endphp
@extends('layouts.admin')
@section('title')
    Belanja Konsinyasi |
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
                <img src="{{asset('assets/images/penarikan.png')}}" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Belanja Konsinyasi</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Halaman Admin</a></li>
                        <li class="breadcrumb-item"><a href="#">Poin of Sales</a></li>
                        <li class="breadcrumb-item active">Belanja Konsinyasi</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="center mb-5">
                    <img src="{{asset('assets/images/'.$data['belanja']->icon)}}" style="width:80px">
                    <h4 class="mt-3">{{$data['keterangan']->label}}</h4>
                    <p>{{$data['keterangan']->keterangan}}</p>
                </div>
                <h5 class="mb-3">Informasi Transaksi</h5>
                <table class="table table-informasi">
                    <tr>
                        <th width="180px">No. Anggota</th>
                        <th width="10px">:</th>
                        <td>{{$data['belanja']->no_anggota}}</td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <th>:</th>
                        <td>{{$data['belanja']->nama_lengkap}}</td>
                    </tr>
                    <tr>
                        <th>No Transaksi</th>
                        <th>:</th>
                        <td>{{$data['belanja']->no_transaksi}}</td>
                    </tr>
                    <tr>
                        <th>Nama Toko</th>
                        <th>:</th>
                        <td>{{$data['belanja']->nama_toko}}</td>
                    </tr>
                    <tr>
                        <th>Total Belanja</th>
                        <th>:</th>
                        <td>Rp {{number_format($data['belanja']->total_pembayaran,0,',','.')}}</td>
                    </tr>
                    <tr>
                        <th>Tenor</th>
                        <th>:</th>
                        <td>{{$data['belanja']->tenor}} Bulan</td>
                    </tr>
                    <tr>
                        <th>Margin</th>
                        <th>:</th>
                        <td>{{$data['belanja']->margin}}% (Rp {{number_format($data['belanja']->nominal_margin,0,',','.')}})</td>
                    </tr>
                    <tr>
                        <th>Angsuran</th>
                        <th>:</th>
                        <td>Rp {{number_format($data['belanja']->angsuran,0,',','.')}}</td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <th>:</th>
                        <td>{{(!empty($data['belanja']->keterangan) ? $data['belanja']->keterangan : 'Tidak ada keterangan')}}</td>
                    </tr>
                </table>
                <div class="alert alert-secondary" role="alert">
                    <h5 class="mb-2">Verifikasi Transaksi</h5>
                    @if($data['belanja']->fid_status==1)
                        <p>Harap segera melakukan verifikasi terhadap pengajuan kredit belanja konsinyasi</p>
                    @else
                        <p>Terimakasih sudah melakukan verifikasi terhadap pengajuan kredit belanja konsinyasi, Silahkan menunggu anggota melakukan konfirmasi penarikan simpanan di kantor koperasi</p>
                    @endif
                    @if($data['belanja']->fid_status==1)
                        <button class="btn btn-danger" onclick="confirm_verifikasi(2)">Ditolak</button>
                        <button class="btn btn-info" onclick="confirm_verifikasi(3)">Disetujui</button>
                    @elseif($data['belanja']->fid_status==3)
                        <button class="btn btn-dark" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
                        <button class="btn btn-primary" onclick="confirm_verifikasi(4)">Selesai</button>
                    @else
                        <button class="btn btn-dark" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <a class="btn btn-dark pull-right" href="{{url('pos/belanja_konsinyasi')}}" >Kembali</a>
            </div>
        </div>
    </div>
    <form action="{{url('pos/belanja_konsinyasi/verifikasi')}}" id="verifikasi_transaksi" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" name="status" id="status">
    </form>
@endsection
@section('js')
    <script>
        function confirm_verifikasi(status){
            if(status==2){
                text="Apakah anda yakin ingin menolak pengajuan kredit belanja konsinyasi ini?";
            }
            else if(status==3){
                text="Apakah anda yakin ingin menerima pengajuan kredit belanja konsinyasi ini?";
            }
            else{
                text="Apakah anda yakin ingin membatalkan verifikasi pengajuan kredit belanja konsinyasi ini?";
            }
            $('#status').val(status);
            Swal.fire({
                title: "Are you sure?",
                type:"question",
                text:text,
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value == true) {
                    $('#verifikasi_transaksi').submit();
                }
            });
        }
    </script>
@endsection
