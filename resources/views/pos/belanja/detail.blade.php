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
                    <p class="text-muted m-0">Menampilkan detail belanja {{$jenis}} anggota</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="{{($data['belanja']->fid_status == 5 ? 'col-md-12' : 'col-md-8')}}">
                <div class="card">
                    <div class="card-body">
                        <div class="center mb-3">
                            <img src="{{asset('assets/images/'.$data['belanja']->icon)}}" style="width:80px">
                            <h4 class="mt-3">{{$data['keterangan']->label}}</h4>
                            <p>{{$data['keterangan']->keterangan}}</p>
                        </div>
                    </div>
                    <div class="card-header" style="background:#eaecef">
                        <ul class="nav nav-pills" role="tablist">
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link {{ $tab == 1 ? 'active' : '' }}" data-toggle="tab" href="#informasi" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                    <span class="d-none d-sm-block">Informasi Transaksi</span>
                                </a>
                            </li>
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link" data-toggle="tab" href="#items" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                    <span class="d-none d-sm-block">Items Belanja</span>
                                </a>
                            </li>
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link {{ $tab == 3 ? 'active' : '' }}" data-toggle="tab" href="#angsuran" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                    <span class="d-none d-sm-block">Angsuran Belanja</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane {{ $tab == 1 ? 'active' : '' }}" id="informasi" role="tabpanel">
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
                                        <td>{{$data['belanja']->no_anggota}}</td>
                                    </tr>
                                    <tr>
                                        <th>Nama Lengkap</th>
                                        <th>:</th>
                                        <td>{{$data['belanja']->nama_lengkap}}</td>
                                    </tr>
                                    @if($jenis=='online')
                                        <tr>
                                            <th>Marketplace Platform</th>
                                            <th>:</th>
                                            <td>{{$data['belanja']->marketplace}}</td>
                                        </tr>
                                    @endif
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
                                <h5 class="mb-3">Items Penjualan</h5>
                                <table class="table table-middle table-hover">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang<hr class="line-xs">Nama Supplier</th>
                                        <th style="text-align:right;width:130px;white-space:nowrap">Harga Beli</th>
                                        <th class="center" style="width:1px;white-space:nowrap">Margin</th>
                                        <th style="text-align:right;width:130px;white-space:nowrap">Total Harga</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data['items'] as $key => $value)
                                        <tr>
                                            <td style="width:1px;white-space:nowrap">{{$key+1}}</td>
                                            <td>
                                                <h6>{{$value->nama_barang}}</h6>
                                                <span>{{$value->nama_supplier}}</span>
                                            </td>
                                            <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->harga_beli,0,',','.')}}</br>
                                            <td class="center">{{$value->margin}}%</td>
                                            <td style="text-align:right;white-space:nowrap">
                                                <span class="text-muted">{{$value->jumlah}} {{$value->satuan}} x Rp {{number_format($value->harga,0,',','.')}}</span>
                                                <h5>Rp {{number_format($value->total,0,',','.')}}</h5>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane {{ $tab == 3 ? 'active' : '' }}" id="angsuran" role="tabpanel">
                                <h5 class="mb-3">Angsuran Kredit Belanja</h5>
                                <table class="table table-middle table-bordered table-hover mt-3">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:1px;white-space:nowrap">Ke</th>
                                        <th style="text-align:right">Angsuran</th>
                                        <th class="center">Payroll Bulan</th>
                                        <th style="text-align:right">Sisa<br>Angsuran</th>
                                        <th>Status</th>
                                        <th>Bayarkan</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php($sudah_muncul = false)
                                    @foreach ($data['angsuran'] as $key => $value)
                                        <tr>
                                            <td>{{$value->angsuran_ke}}</td>
                                            <td style="text-align:right">{{number_format($value->total_angsuran,'0',',','.')}}</td>
                                            <td class="center" >
                                                @if($value->fid_payroll !== '')
                                                    {{ \App\Helpers\GlobalHelper::nama_bulan($value->bulan) }}
                                                @endif
                                                @if($value->fid_status == 3)
                                                    Belum Dibayar
                                                @endif
                                                @if($value->fid_status == 6 && $value->fid_payroll === null)
                                                    Sudah Dibayar
                                                @endif
                                            </td>
                                            <td style="text-align:right">{{number_format($value->sisa_angsuran,'0',',','.')}}</td>
                                            <td class="center" style="width:1px;white-space:nowrap">
                                                <span style="background:{{$value->color}};padding:3px 6px;color:#fff;font-size:11px">{{$value->status_angsuran}}</span>
                                            </td>
                                            <td class="py-1 align-middle text-center" style="width: 50px;">
                                                @if($value->fid_status == 3 && $sudah_muncul == false)
                                                    @php($sudah_muncul = true)
                                                    <a href="{{ url('pos/belanja/' . $jenis . '/bayar/'. $id .'/' . $value->id) }}" class="btn btn-sm btn-primary" >Bayar</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a class="btn btn-dark pull-right" href="{{url('pos/belanja/'.$jenis)}}" >Kembali</a>
                        {{-- @if($data['belanja']->fid_status <= 2 )
                        <a class="btn btn-primary"  href="{{url('pos/belanja/'.$jenis.'/form?id='.$id)}}">Ubah Data</a>
                        <button class="btn btn-danger" onclick="confirm_verifikasi(5)">Batalkan</button>
                        @endif --}}
                    </div>
                </div>
            </div>
            @if($data['belanja']->fid_status!=5)
                <div class="col-md-4">
                    <div style="position:sticky;top:100px;width:100%;z-index:100">
                        @if($data['belanja']->fid_status <= 2 )
                            <div class="alert alert-secondary mb-5" role="alert">
                                <h5 class="mb-2">Konfirmasi Transkasi</h5>
                                <p>Transkasi masih dalam proses pembayaran, silahkan ubah atau batalkan transkasi sebelum transksi ini selesai</p>
                                <a class="btn btn-primary" href="{{url('pos/belanja/'.$jenis.'/form?id='.$id)}}" >Edit Transaksi</a>
                                <button class="btn btn-danger" onclick="confirm_verifikasi(5)">Batalkan</button>
                            </div>
                        @endif
                        <div class="alert alert-secondary mb-5" role="alert">
                            <h5 class="mb-2">Verifikasi Transkasi</h5>
                            @if($data['belanja']->fid_status==1)
                                <p>Harap segera melakukan verifikasi terhadap pengajuan kredit belanja {{$jenis}} ini.</p>
                            @elseif($data['belanja']->fid_status==2)
                                <p>Terimakasih sudah melakukan verifikasi terhadap pengajuan kredit belanja {{$jenis}}, Silahkan batalkan verifikasi jika ingin mengubah keputusan verifikasi</p>
                            @elseif($data['belanja']->fid_status==3)
                                <p>Terimakasih sudah melakukan verifikasi terhadap terhadap pengajuan kredit belanja {{$jenis}}, Silahkan menunggu anggota berkunjung ke koperasi untuk melakikan konfirmasi penarikan simpanan</p>
                            @else
                                <p>Terimakasih sudah menyelesaikan proses pengajuan kredit belanja {{$jenis}}</p>
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

                </div>
            @endif
        </div>
    </div>
    <form action="{{url('pos/belanja/'.$jenis.'/verifikasi')}}" id="verifikasi_transaksi" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" name="status" id="status">
    </form>
@endsection
@section('js')
    <script>
        function confirm_verifikasi(status){
            if(status==2){
                text="Apakah anda yakin ingin menolak pengajuan kredit belanja {{$jenis}} ini?";
            }
            else if(status==3){
                text="Apakah anda yakin ingin menyetujui pengajuan kredit belanja {{$jenis}} ini?";
            }
            else if(status==4){
                text="Apakah anda yakin ingin menyelesaikan pengajuan kredit belanja {{$jenis}} ini?";
            }
            else if(status==5){
                text="Apakah anda yakin ingin membatalkan pengajuan kredit belanja {{$jenis}} ini?";
            }
            else{
                text="Apakah anda yakin ingin membatalkan verifikasi pengajuan kredit belanja {{$jenis}} ini?";
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
