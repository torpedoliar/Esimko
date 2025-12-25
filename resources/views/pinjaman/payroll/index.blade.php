@php
    $app='sinjam';
    $page='Payroll Angsuran';
    $subpage='Payroll Angsuran';

    if(!empty($data['payroll'])){
      if($data['payroll']->fid_status==1 || $data['payroll']->fid_status==0){
        $disabled=$data['status'];
      }
      else{
        $disabled='disabled';
      }
    }
    else{
      $disabled=$data['status'];
    }
@endphp
@extends('layouts.admin')
@section('title')
    Payroll Angsuran  |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/pay-day.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Payroll Angsuran</h4>
                        <p class="text-muted m-0">Menampilkan data payroll angsuran pinjaman anggota yang sudah diproses oleh petugas</p>
                    </div>
                </div>
            </div>
            <form action="" method="get" id="form_search">
                <div class="row">
                    <div class="col-md-2">
                        <form action="" method="get">
                            <input type="text" name="bulan" id="bulan" class="form-control monthpicker" value="{{$bulan}}" onchange="javascript:submit()" autocomplete="off">
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Anggota">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary btn-block" id="button_confirm_proses" @if($disabled=='disabled') {{$disabled}} @else onclick="confirm_proses()" @endif>Proses Payroll</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-success btn-block dropdown-toggle" type="button" onclick="export_excel()">Export</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-danger btn-block dropdown-toggle" type="button" onclick="confirm_hapus()">Hapus</button>
                    </div>
                </div>
            </form>
        </div>
        @if($data['payroll']==null)
            <div style="width:100%;text-align:center">
                <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
                <h4 class="mt-3">Payroll Angsuran Belum Diproses</h4>
            </div>
        @else
            <div class="row mt-4 mb-4">
                <div class="col-md-8">
                    <div style="height:100%;width:100%">
                        @if(count($data['payroll']->data)==0)
                            <div style="width:100%;text-align:center">
                                <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
                                <h4 class="mt-3">Data Anggota tidak Ditemukan</h4>
                            </div>
                        @else
                            <h1 class="text-center" id="loading_state" style="display: none;">Loading <i class="fa fa-spinner fa-spin"></i><br></h1>
                            <div class="table-responsive">
                                <table class="table table-middle table-custom">
                                    <thead>
                                    <tr>
                                        <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
                                        <th class="center">Jenis Pinjaman</th>
                                        <th style="text-align:right;white-space:nowrap">Angs. Pokok<hr class="line-xs">Angs. Bunga</th>
                                        <th style="text-align:right;white-space:nowrap">Total Angsuran <hr class="line-xs"> Angsuran Ke</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data['payroll']->data as $key => $value)
                                        <tr>
                                            <td>
                                                <div class="media">
                                                    <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                                                        <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
                                                    </div>
                                                    <div class="media-body align-self-center">
                                                        <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                                                        <h5 class="text-truncate font-size-15"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->nama_lengkap}}</a></h5>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="center">{{str_replace('Pinjaman','',$value->jenis_transaksi)}}</td>
                                            <td style="text-align:right;white-space:nowrap">
                                                <div style="font-weight:500">Rp {{number_format(\App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_pokok),'0',',','.')}}</div>
                                                <div class="text-muted">Rp {{number_format($value->bunga,'0',',','.')}}</div>
                                            </td>
                                            <td style="text-align:right;white-space:nowrap;">
                                                <div style="font-weight:500">Rp {{number_format(\App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_pokok)+$value->bunga,'0',',','.')}}</div>
                                                <div class="text-muted">{{$value->angsuran_ke}} dari {{$value->tenor}}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $data['payroll']->data->links('include.pagination', ['pagination' => $data['payroll']->data] ) }}
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="border-left:1px solid #dedede;padding:20px 20px;height:100%;width:100%">
                        <div class="center">
                            <img src="{{asset('assets/images/'.$data['payroll']->icon)}}" style="width:65px" class="">
                            <h5 class="mb-2 mt-2">{{$data['payroll']->status}}</h5>
                            <p>{{$data['payroll']->keterangan}}</p>
                            <a href="" class="btn btn-secondary" >Cetak Payroll</a>
                            @if($data['payroll']->fid_status==1)
                                <button class="btn btn-warning" onclick="confirm_payroll(2)">Sudah Dikirim</button>
                            @elseif($data['payroll']->fid_status==2)
                                <button class="btn btn-info" onclick="confirm_payroll(3)">Konfirmasi Pembayaran</button>
                            @else
                                <button class="btn btn-danger" onclick="confirm_payroll(1)">Batalkan Verifikasi</button>
                            @endif
                        </div>
                        <h5 class="mb-3 mt-5">Riwayat Transaksi</h5>
                        <ul class="verti-timeline list-unstyled">
                            <li class="event-list">
                                <div class="event-timeline-dot">
                                    <i class="bx bx-right-arrow-circle"></i>
                                </div>
                                <h6>{{\App\Helpers\GlobalHelper::tgl_indo($data['payroll']->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($data['payroll']->created_at,'H:i:s')}}</h6>
                                <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500">{{$data['payroll']->nama_lengkap}}</span></p>
                            </li>
                            @foreach (\App\Helpers\GlobalHelper::get_verifikasi_transaksi($data['payroll']->id,'payroll_pinjaman') as $key => $value)
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
                </div>
            </div>
        @endif
    </div>
    <form action="{{url('pinjaman/payroll/verifikasi')}}" method="post" id="verifikasi_payroll" >
        {{ csrf_field() }}
        <input type="hidden" name="bulan" value="{{$bulan}}">
        <input type="hidden" name="status" id="status">
    </form>
    <form action="{{url('pinjaman/payroll/proses')}}" id="proses_angsuran" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="bulan" value="{{$bulan}}">
    </form>
@endsection
@section('js')
    <script>
        function confirm_proses(){
            Swal.fire({
                title: "Are you sure?",
                text: "Apakah anda yakin ingin memproses simpanan anggota pada bulan ini",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Proses Payroll'
            }).then((result) => {
                if (result.value == true) {
                    $('#button_confirm_proses').html('Loading ... ').attr('disabled', 'disabled');
                    Swal.fire({
                        title: 'Loading',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => Swal.showLoading()
                    });
                    $('#proses_angsuran').submit();
                }
            });
        }

        function confirm_payroll(status){
            if(status==2){
                text="Apakah Data Payroll Angsuran sudah dikirim ke HRD Perusahaan?";
            }
            else if(status==3){
                text="Apakah Pembayaran Payroll Angsuran dari Perusahaan sudah diterima oleh Koperasi?";
            }
            else{
                text="Apakah anda yakin ingin membatalkan Payroll Angsuran ini agar bisa diproses ulang";
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
                    $('#loading_state').show();
                    $('#verifikasi_payroll').submit();
                }
            });
        }

        let confirm_hapus = () => {
            Swal.fire({
                title: "Hapus Payroll?",
                text: "Apakah anda yakin ingin menghapus payroll ?",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Ya'
            }).then((result) => {
                if (result.value === true) proses_hapus();
            });
        }

        let proses_hapus = () => {
            Swal.fire({
                title: "Konfirmasi Terakhir?",
                text: "Apakah benar benar yakin ingin menghapus payroll bulan ini ?",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Proses Hapus'
            }).then((result) => {
                if (result.value === true) {
                    let bulan = $('#bulan').val();
                    window.location.href = "{{ url('pinjaman/payroll/hapus') }}?bulan=" + bulan;
                }
            });
        }

        let export_excel = () => {
            let data = $('#form_search').serialize();
            window.open("{{ url('pinjaman/payroll/export') }}?" + data, '_blank');
        }
    </script>
@endsection
