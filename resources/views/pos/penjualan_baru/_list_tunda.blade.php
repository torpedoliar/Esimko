<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>No. Penjualan<hr class="line-xs">Waktu</th>
            <th>Pembeli</th>
            <th class="center">Jumlah<br>Barang</th>
            <th class="center">Metode<br>Pembayaran</th>
            <th style="text-align:right">Total<br>Pembayaran</th>
            <th>Kasir</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($list_penjualan as $key => $value)
            <tr>
                <td style="width:1px;white-space:nowrap;border-color:{{$value->color}}" onclick="resume_penjualan('{{ $value->no_transaksi }}')">
                    <h6>{{$value->no_transaksi}}</h6>
                    {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'d/m/Y, H:i:s')}}
                </td>
                <td onclick="resume_penjualan('{{ $value->no_transaksi }}')">
                    <div class="media">
                        <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                            <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
                        </div>
                        <div class="media-body align-self-center">
                            @if(!empty($value->anggota))
                                <p class="text-muted mb-0">No. {{$value->anggota->no_anggota}}</p>
                                <h5 class="text-truncate font-size-13"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->anggota->nama_lengkap}}</a></h5>
                            @else
                                <p class="text-muted mb-0">No. 0000</p>
                                <h5 class="text-truncate font-size-13">Bukan Anggota</h5>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="center" onclick="resume_penjualan('{{ $value->no_transaksi }}')">{{$value->items->sum('jumlah')}}</td>
                <td class="center" onclick="resume_penjualan('{{ $value->no_transaksi }}')">{{$value->metode_pembayaran->keterangan}}</td>
                <td style="text-align:right;white-space:nowrap" onclick="resume_penjualan('{{ $value->no_transaksi }}')">Rp {{number_format($value->items->sum('total'),0,',','.')}}</td>
                <td style="width:1px;white-space:nowrap" onclick="resume_penjualan('{{ $value->no_transaksi }}')">
                    @if($value->kasir==null)
                        <span>Belum Diproses<br>oleh Kasir</span>
                    @else
                        <span class="text-muted">No. {{$value->kasir}}</span>
                        <h6>{{$value->nama_petugas}}</h6>
                    @endif
                </td>
                <td style="width:1px;white-space:nowrap">
                    <a href="javascript:void(0)" class="text-dark" onclick="hapus_penjualan('{{ $value->id }}')"><i class="bx bx-trash h3 m-0 mr-3"></i></a>
                    <a href="javascript:void(0)" class="text-dark" onclick="resume_penjualan('{{ $value->no_transaksi }}')"><i class="bx bx-right-arrow h3 m-0"></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
