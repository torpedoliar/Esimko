@php
    $page='Simpanan';
    $subpage='Simpanan Anggota';
@endphp
@extends('layouts.main')
@section('title')
    Simpanan |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 font-size-18">Simpanan Anggota</h4>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-2">
                        <form action="" method="get">
                            <input type="text" name="bulan" class="form-control monthpicker" value="{{$bulan}}" onchange="javascript:submit()" autocomplete="off">
                        </form>
                    </div>
                    <div class="col-md-7">
                        <form action="" method="get">
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Anggota">
                                <div class="input-group-append">
                                    <button class="btn btn-dark" type="submit">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-block" onclick="confirm_proses()">Proses Simpanan</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(count($data['simpanan-anggota'])==0)
                    <div style="width:100%;text-align:center">
                        <img src="{{asset('assets/images/employees-not-found.png')}}" class="mt-3" style="width:250px">
                        <h4 class="mt-2">ANGGOTA TIDAK DITEMUKAN</h4>
                    </div>
                @elseif($data['payroll']->label=='Simpanan Anggota Belum Diproses')
                    <div style="width:100%;text-align:center">
                        <img src="{{asset('assets/images/employees-not-found.png')}}" class="mt-3" style="width:250px">
                        <h4 class="mt-2">{{$data['payroll']->label}}</h4>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-middle table-bordered table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th rowspan="2" class="center">No</th>
                                <th rowspan="2">No. Anggota<hr style="margin-top: 0.5rem;margin-bottom: 0.5rem;">Nama Lengkap</th>
                                <th rowspan="2">Divisi / Bagian</th>
                                <th colspan="{{count($data['jenis-simpanan'])}}" class="center">Jumlah Simpanan</th>
                                <th rowspan="2" class="center">Tanggal</th>
                                <th rowspan="2" class="center">Status</th>
                            </tr>
                            <tr>
                                @foreach ($data['jenis-simpanan'] as $key => $value)
                                    <th style="text-align:right;width:100px">{{$value->jenis_simpanan}}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($data['simpanan-anggota'] as $key => $value)
                                <tr>
                                    <td class="center">{{ $data['simpanan-anggota']->firstItem() + $key }}</td>
                                    <td>
                                        <div class="media">
                                            <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle img-thumbnail avatar-sm mr-2">
                                            <div class="media-body align-self-center">
                                                <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                                                <h5 class="text-truncate font-size-15"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->nama_lengkap}}</a></h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{$value->divisi}}</div>
                                        <div>{{$value->bagian}}</div>
                                    </td>
                                    @foreach ($data['jenis-simpanan'] as $key2 => $value2)
                                        @php
                                            $label=str_replace(' ','_',strtolower($value2->jenis_simpanan));
                                        @endphp
                                        <td style="text-align:right">{{number_format($value->$label,'0','.',',')}}</td>
                                    @endforeach
                                    <td class="center">{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</td>
                                    <td class="center">
                                        <span style="background:{{$value->color}};padding:3px 6px;color:#fff;font-size:11px">{{$value->status_simpanan}}</span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:20px">
                        {{ $data['simpanan-anggota']->links('include.pagination', ['pagination' => $data['simpanan-anggota']] ) }}
                    </div>
                    <div class="alert alert-{{$data['payroll']->color}} mt-3 mb-0" style="text-align:left" role="alert">
                        <h5 class="mb-2">{{$data['payroll']->label}}</h5>
                        <p class="mb-2">{{$data['payroll']->keterangan}}</p>
                        @if($data['payroll']->fid_status==1)
                            <button class="btn btn-primary" onclick="confirm_verifikasi(3)">Diterima</button>
                            <button class="btn btn-danger" onclick="confirm_verifikasi(2)">Ditolak</button>
                        @else
                            <button class="btn btn-secondary" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
                        @endif
                    </div>

                    <form action="{{url('simpanan/anggota/verifikasi')}}" method="post" id="verifikasi_payroll" >
                        {{ csrf_field() }}
                        <input type="hidden" name="bulan" value="{{$bulan}}">
                        <input type="hidden" name="status" id="status">
                    </form>
                @endif
            </div>
        </div>
    </div>
    <form action="{{url('simpanan/anggota/proses')}}" id="proses_simpanan" method="post">
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
                confirmButtonText: 'Proses Simpanan'
            }).then((result) => {
                if (result.value == true) {
                    $('#proses_simpanan').submit();
                }
            });
        }

        function confirm_verifikasi(status){
            if(status==2){
                text="Apakah anda yakin ingin membatalkan Verifikasi Data Simpanan Anggota ini";
            }
            else if(status==2){
                text="Apakah anda yakin ingin menolak Data Simpanan Anggota ini";
            }
            else{
                text="Apakah anda yakin ingin menerima Data Simpanan Anggota ini";
            }
            $('#status').val(status);
            Swal.fire({
                title: "Are you sure?",
                type:"question",
                text:text,
                showCancelButton: true,
                confirmButtonColor: '#d63030',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value == true) {
                    $('#verifikasi_payroll').submit();
                }
            });
        }
    </script>
@endsection
