@php
    $app='pos';
    $page='Penjualan';
    $subpage='Penjualan';
@endphp
@extends('layouts.admin')
@section('title')
    Penjualan |
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
                <img src="{{asset('assets/images/icon-page/market.png')}}" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">DATA {{$page}}</h4>
                    <p class="text-muted m-0">Menampilkan detail Penjualan toko</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="center mb-5">
                            <img src="{{asset('assets/images/'.$data['belanja']->icon)}}" style="width:80px">
                            {{-- <img src="{{asset('assets/images/'.$data['penjualan']->icon)}}" style="width:80px"> --}}
                            <h4 class="mt-3">{{$data['keterangan']->label}}</h4>
                            <p>{{$data['keterangan']->keterangan}}</p>
                        </div>
                    </div>
                    <div class="card-header" style="background:#eaecef">
                        <ul class="nav nav-pills" role="tablist">
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link active" data-toggle="tab" href="#informasi" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                    <span class="d-none d-sm-block">Informasi Transaksi</span>
                                </a>
                            </li>
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link" data-toggle="tab" href="#items" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                    <span class="d-none d-sm-block">Items Penjualan</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="informasi" role="tabpanel">
                                <h5 class="mb-3">Informasi Transaksi</h5>
                                <table class="table table-informasi">
                                    <tr>
                                        <th>No Transaksi</th>
                                        <th>:</th>
                                        <td>{{$data['belanja']->no_transaksi}}</td>
                                    </tr>
                                    <tr>
                                        <th width="180px">No. Anggota</th>
                                        <th width="10px">:</th>
                                        <td>{{(!empty($data['belanja']->no_anggota) ? $data['belanja']->no_anggota : 'Bukan Anggota')}}</td>
                                    </tr>
                                    <tr>
                                        <th>Nama Lengkap</th>
                                        <th>:</th>
                                        <td>{{(!empty($data['belanja']->nama_lengkap) ? $data['belanja']->nama_lengkap : 'Bukan Anggota')}}</td>
                                    </tr>
                                    <tr>
                                        <th>Metode Pembayaran</th>
                                        <th>:</th>
                                        <td>{{$data['belanja']->metode_pembayaran}}</td>
                                    </tr>
                                    <tr>
                                        <th>Total Belanja</th>
                                        <th>:</th>
                                        <td>Rp {{number_format($data['belanja']->total_pembayaran,0,',','.')}}</td>
                                    </tr>
                                    @if($data['belanja']->voucher_nominal!=0)
                                        <tr>
                                            <th>Kode Voucher</th>
                                            <th>:</th>
                                            <td>{{$data['belanja']->kode_voucher}}</td>
                                        </tr>
                                        <tr>
                                            <th>Voucher Belanja</th>
                                            <th>:</th>
                                            <td>Rp {{number_format($data['belanja']->voucher_nominal,0,',','.')}}</td>
                                        </tr>
                                    @endif
                                    <tr class="data_3 data_hide">
                                        <th>Kode Voucher</th>
                                        <th>:</th>
                                        <td>{{$data['belanja']->kode_voucher}}</td>
                                    </tr>
                                    <tr class="data_3 data_hide">
                                        <th>Voucher Persen</th>
                                        <th>:</th>
                                        <td>{{$data['belanja']->voucher_persen}}</td>
                                    </tr>
                                    <tr class="data_3 data_hide">
                                        <th>Voucher Nominal</th>
                                        <th>:</th>
                                        <td>{{$data['belanja']->voucher_nominal}}</td>
                                    </tr>
                                    <tr class="data_1 data_hide">
                                        <th>Tunai</th>
                                        <th>:</th>
                                        <td>Rp {{number_format($data['belanja']->tunai,0,',','.')}}</td>
                                    </tr>
                                    <tr class="data_1 data_hide">
                                        <th>Kembali</th>
                                        <th>:</th>
                                        <td>Rp {{number_format($data['belanja']->kembali,0,',','.')}}</td>
                                    </tr>
                                    <tr class="data_3 data_hide">
                                        <th>Tenor</th>
                                        <th>:</th>
                                        <td>{{$data['belanja']->tenor}} Bulan</td>
                                    </tr>
                                    <tr class="data_3 data_hide">
                                        <th>Angsuran</th>
                                        <th>:</th>
                                        <td>Rp {{number_format($data['belanja']->angsuran,0,',','.')}}</td>
                                    </tr>
                                    <tr class="data_3 data_hide">
                                        <th>Status Angsuran</th>
                                        <th>:</th>
                                        <td>Belum Lunas</td>
                                    </tr>
                                    <tr class="data_5 data_hide">
                                        <th>Nomor Debet Card </th>
                                        <th>:</th>
                                        <td>{{$data['belanja']->no_debit_card}}</td>
                                    </tr>
                                    <tr class="data_7 data_hide">
                                        <th>Nomor Akun {{$data['belanja']->metode_pembayaran}}</th>
                                        <th>:</th>
                                        <td>{{$data['belanja']->account_number}}</td>
                                    </tr>
                                    <tr>
                                        <th>Keterangan</th>
                                        <th>:</th>
                                        <td>{{(!empty($data['belanja']->keterangan) ? $data['belanja']->keterangan : 'Tidak ada keterangan')}}</td>
                                    </tr>
                                </table>
                                <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
                                <ul class="verti-timeline list-unstyled">
                                    <li class="event-list">
                                        <div class="event-timeline-dot">
                                            <i class="bx bx-right-arrow-circle"></i>
                                        </div>
                                        <h6>{{\App\Helpers\GlobalHelper::tgl_indo($data['belanja']->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($data['belanja']->created_at,'H:i:s')}}</h6>
                                        <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500">{{$data['belanja']->nama_petugas}}</span></p>
                                    </li>
                                    @foreach (\App\Helpers\GlobalHelper::get_verifikasi_transaksi($id,'penjualan') as $key => $value)
                                        <li class="event-list">
                                            <div class="event-timeline-dot">
                                                <i class="bx bx-right-arrow-circle"></i>
                                            </div>
                                            <h6>{{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}</h6>
                                            <p class="text-muted">{{$value->caption}} <span style="font-weight:500">{{$value->nama_lengkap}}</span></p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="tab-pane" id="items" role="tabpanel">
                                <table class="table table-middle table-hover">
                                    <thead class="thead-light">
                                    <tr>
                                        <th width="20px">No</th>
                                        <th>Nama Barang</th>
                                        <th style="text-align:right;width:150px">Total Harga</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data['items'] as $key => $value)
                                        <tr>
                                            <td width="20px">{{$key+1}}</td>
                                            <td>
                                                <div class="media">
                                                    <div class="rounded mr-3 produk-wrapper" style="height:60px;width:60px">
                                                        <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                                                    </div>
                                                    <div class="align-self-center media-body">
                                                        <span>Kode. {{$value->kode}}</span>
                                                        <h6>{{$value->nama_produk}}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align:right;white-space:nowrap">
                                                <span class="text-muted">{{$value->jumlah}} {{$value->satuan}} x Rp {{number_format($value->harga,0,',','.')}}</span>
                                                <h5>Rp {{number_format($value->total,0,',','.')}}</h5>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="pull-right">
                            <a class="btn btn-dark" href="{{url('pos/penjualan')}}" >Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="position:sticky;top:100px;width:100%;z-index:100">
                    @if($data['belanja']->fid_status == 1)
                        <div class="alert alert-secondary mb-5" role="alert">
                            <h5 class="mb-2">Konfirmasi Transkasi</h5>
                            <p>Transkasi masih dalam proses pembayaran, silahkan ubah atau batalkan transkasi sebelum transksi ini selesai</p>
                            <a class="btn btn-primary" href="{{url('pos/penjualan/form?id='.$id)}}" >Edit Transaksi</a>
                            <a class="btn btn-danger" href="{{url('pos/penjualan')}}" >Batalkan</a>
                        </div>
                    @elseif($data['belanja']->fid_status == 2 )
                        <div class="alert alert-secondary" role="alert">
                            <h5 class="mb-2">Cetak Bukti Transkasi</h5>
                            <p>Transaksi penjualan sudah selesai dilakukan, silahkan cetak bukti transkasi</p>
                            <a class="btn btn-secondary" target="_blank" href="{{url('pos/penjualan/cetak_struk?id='.$id)}}" >Cetak Struk</a>
                        </div>
                    @elseif($data['belanja']->fid_status == 3 )
                        <div class="alert alert-secondary mb-5" role="alert">
                            <h5 class="mb-2">Transaksi Dibatalkan</h5>
                            <p>Transkasi sudah dibatalkan, anda tidak bisa membuka atau melanjutkan transksi ini. Silahkan membuat transaksi baru</p>
                        </div>
                    @else
                        <div class="alert alert-secondary" role="alert">
                            <h5 class="mb-2">Cetak Bukti Transkasi</h5>
                            <p>Transaksi penjualan sudah selesai dilakukan, silahkan cetak bukti transkasi</p>
                            <a class="btn btn-secondary" target="_blank" href="{{url('pos/penjualan/cetak_struk?id='.$id)}}" >Cetak Struk</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        $('.data_hide').hide();
        $('.data_{{$data['belanja']->fid_metode_pembayaran}}').show();
    </script>
@endsection
