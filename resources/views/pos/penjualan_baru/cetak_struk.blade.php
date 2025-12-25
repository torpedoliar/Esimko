@extends('layouts.report')
@section('title')
    Cetak Struk Penjualan
@endsection
@section('css')
    <style>
        .container-struk{
            font-size:10pt
        }
        .container-struk .header{
            text-align:center;
            font-size:14pt;
            border-bottom:1px solid #222222;
            padding-bottom:20px
        }

        .container-struk .informasi{
            border-bottom:1px solid #222222;
            padding-bottom:10px;
            padding-top:10px
        }

        .container-struk .items{
            border-bottom:1px solid #222222;
            padding-bottom:10px;
            padding-top:10px
        }

        .container-struk .items table tr th{
            text-align: left;
        }

        .container-struk .accounting{
            border-bottom:1px solid #222222;
            padding-top:20px
        }

        .container-struk .footer{
            text-align:center;
            font-size:12pt;
            font-weight:600;
            padding-top: 30px
        }


    </style>
@endsection
@section('content')
    <div class="container-struk">
        <div class="header">
            <div>CO-OP MART</div>
            <div>Kopkar Satya Sejahtera</div>
            <div>Ruko Citra Harmoni</div>
        </div>
        <div class="informasi">
            <table style="width:100%">
                <tr>
                    <td width="90px">Nota</td>
                    <td>:</td>
                    <td>{{$penjualan->no_transaksi}}</td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>{{\App\Helpers\GlobalHelper::tgl_indo($penjualan->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($penjualan->created_at,'H:i:s')}}</td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td>@if(!empty($penjualan->anggota))({{$penjualan->anggota->no_anggota}}) {{$penjualan->anggota->nama_lengkap}} @else Non Member @endif</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>:</td>
                    <td>{{$penjualan->user_kasir->nama_lengkap ?? ''}}</td>
                </tr>
            </table>
        </div>
        <div class="items">
            <table style="width:100%">
                @foreach ($penjualan->items as $key => $value)
                    <tr>
                        <th colspan="3">{{$value->produk->nama_produk}}</th>
                        <th style="text-align:right">{{$value->produk->satuan}}</th>
                    </tr>
                    <tr>
                        <td>{{$value->jumlah}}</td>
                        <td style="text-align:right">{{number_format($value->harga,2,'.',',')}}</td>
                        <td style="text-align:right">{{number_format($value->nominal_diskon,2,'.',',')}}</td>
                        <td style="text-align:right">{{number_format($value->total,2,'.',',')}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total Tanpa Diskon</td>
                    <td style="text-align:right">{{number_format($penjualan->total_tanpa_diskon,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Total Diskon Barang</td>
                    <td style="text-align:right">{{number_format($penjualan->total_diskon,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align:right">{{number_format($penjualan->subtotal,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Diskon Transaksi</td>
                    <td style="text-align:right">{{number_format($penjualan->diskon,2,'.',',')}}</td>
                </tr>
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total</td>
                    <td style="text-align:right">{{number_format($penjualan->total_pembayaran,2,'.',',')}}</td>
                </tr>
                @if($penjualan->fid_metode_pembayaran==1)
                    <tr>
                        <td>Tunai</td>
                        <td style="text-align:right">{{number_format($penjualan->tunai,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Kembali</td>
                        <td style="text-align:right">{{number_format($penjualan->tunai - $penjualan->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                @elseif($penjualan->fid_metode_pembayaran==3)
                    <tr>
                        <td>Kredit</td>
                        <td style="text-align:right">{{number_format($penjualan->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                @elseif($penjualan->fid_metode_pembayaran==5)
                    <tr>
                        <td>{{$penjualan->metode_pembayaran}}</td>
                        <td style="text-align:right">{{number_format($penjualan->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Nomor Debit Card</td>
                        <td style="text-align:right">{{$penjualan->no_debit_card}}</td>
                    </tr>
                @elseif($penjualan->fid_metode_pembayaran==7)
                    <tr>
                        <td>{{$penjualan->metode_pembayaran}}</td>
                        <td style="text-align:right">{{number_format($penjualan->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Nomor Akun</td>
                        <td style="text-align:right">{{$penjualan->account_number}}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="footer">
            <div>Terimakasih</div>
            <div>Silahkan datang kembali</div>
            <div>Cek Kembali Belanjaan anda</div>
            <div>Komplain tidak dilayani setelah meninggalkan toko</div>
        </div>
    </div>

    <div class="container-struk" style="page-break-before: always;">
        <div class="header">
            <div>CO-OP MART</div>
            <div>Kopkar Satya Sejahtera</div>
            <div>Ruko Citra Harmoni</div>
        </div>
        <div class="informasi">
            <table style="width:100%">
                <tr>
                    <td width="90px">Nota</td>
                    <td>:</td>
                    <td>{{$penjualan->no_transaksi}}</td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>{{\App\Helpers\GlobalHelper::tgl_indo($penjualan->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($penjualan->created_at,'H:i:s')}}</td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td>@if(!empty($penjualan->anggota))({{$penjualan->anggota->no_anggota}}) {{$penjualan->anggota->nama_lengkap}} @else Non Member @endif</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>:</td>
                    <td>{{$penjualan->user_kasir->nama_lengkap ?? ''}}</td>
                </tr>
            </table>
        </div>
        <div class="items">
            <table style="width:100%">
                @foreach ($penjualan->items as $key => $value)
                    <tr>
                        <th colspan="3">{{$value->produk->nama_produk}}</th>
                        <th style="text-align:right">{{$value->produk->satuan}}</th>
                    </tr>
                    <tr>
                        <td>{{$value->jumlah}}</td>
                        <td style="text-align:right">{{number_format($value->harga,2,'.',',')}}</td>
                        <td style="text-align:right">{{number_format($value->nominal_diskon,2,'.',',')}}</td>
                        <td style="text-align:right">{{number_format($value->total,2,'.',',')}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total Tanpa Diskon</td>
                    <td style="text-align:right">{{number_format($penjualan->total_tanpa_diskon,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Total Diskon Barang</td>
                    <td style="text-align:right">{{number_format($penjualan->total_diskon,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align:right">{{number_format($penjualan->subtotal,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Diskon Transaksi</td>
                    <td style="text-align:right">{{number_format($penjualan->diskon,2,'.',',')}}</td>
                </tr>
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total</td>
                    <td style="text-align:right">{{number_format($penjualan->total_pembayaran,2,'.',',')}}</td>
                </tr>
                @if($penjualan->fid_metode_pembayaran==1)
                    <tr>
                        <td>Tunai</td>
                        <td style="text-align:right">{{number_format($penjualan->tunai,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Kembali</td>
                        <td style="text-align:right">{{number_format($penjualan->kembali,2,'.',',')}}</td>
                    </tr>
                @elseif($penjualan->fid_metode_pembayaran==3)
                    <tr>
                        <td>Kredit</td>
                        <td style="text-align:right">{{number_format($penjualan->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                @elseif($penjualan->fid_metode_pembayaran==5)
                    <tr>
                        <td>{{$penjualan->metode_pembayaran}}</td>
                        <td style="text-align:right">{{number_format($penjualan->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Nomor Debit Card</td>
                        <td style="text-align:right">{{$penjualan->no_debit_card}}</td>
                    </tr>
                @elseif($penjualan->fid_metode_pembayaran==7)
                    <tr>
                        <td>{{$penjualan->metode_pembayaran}}</td>
                        <td style="text-align:right">{{number_format($penjualan->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Nomor Akun</td>
                        <td style="text-align:right">{{$penjualan->account_number}}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="footer">
            <div>Terimakasih</div>
            <div>Silahkan datang kembali</div>
            <div>Cek Kembali Belanjaan anda</div>
            <div>Komplain tidak dilayani setelah meninggalkan toko</div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        window.print();
        $(window).keydown(function(event){
            console.log(event.keyCode);
            if (event.keyCode == 13) window.location.href = "{{ url('pos/penjualan_baru') }}";
        });
    </script>
@endsection
