@extends('layouts.report')
@section('title')
    Cetak Struk Pembelian
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
                    <td>{{$pembelian->no_transaksi}}</td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>{{\App\Helpers\GlobalHelper::tgl_indo($pembelian->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($pembelian->created_at,'H:i:s')}}</td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td>@if(!empty($pembelian->anggota))({{$pembelian->anggota->no_anggota}}) {{$pembelian->anggota->nama_lengkap}} @else Non Member @endif</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>:</td>
                    <td>{{$pembelian->nama_petugas}}</td>
                </tr>
            </table>
        </div>
        <div class="items">
            <table style="width:100%">
                @foreach ($pembelian->items as $key => $value)
                    <tr>
                        <th colspan="3">{{$value->produk->nama_produk ?? ''}}</th>
                        <th style="text-align:right">{{$value->produk->satuan ?? ''}}</th>
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
                    <td style="text-align:right">{{number_format($pembelian->total_tanpa_diskon,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Total Diskon Barang</td>
                    <td style="text-align:right">{{number_format($pembelian->total_diskon,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align:right">{{number_format($pembelian->subtotal,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Diskon Transaksi</td>
                    <td style="text-align:right">{{number_format($pembelian->diskon,2,'.',',')}}</td>
                </tr>
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total</td>
                    <td style="text-align:right">{{number_format($pembelian->total_pembayaran,2,'.',',')}}</td>
                </tr>
                @if($pembelian->fid_metode_pembayaran==1)
                    <tr>
                        <td>Tunai</td>
                        <td style="text-align:right">{{number_format($pembelian->tunai,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Kembali</td>
                        <td style="text-align:right">{{number_format($pembelian->tunai - $pembelian->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                @elseif($pembelian->fid_metode_pembayaran==3)
                    <tr>
                        <td>Kredit</td>
                        <td style="text-align:right">{{number_format($pembelian->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                @elseif($pembelian->fid_metode_pembayaran==5)
                    <tr>
                        <td>{{$pembelian->metode_pembayaran}}</td>
                        <td style="text-align:right">{{number_format($pembelian->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Nomor Debit Card</td>
                        <td style="text-align:right">{{$pembelian->no_debit_card}}</td>
                    </tr>
                @elseif($pembelian->fid_metode_pembayaran==7)
                    <tr>
                        <td>{{$pembelian->metode_pembayaran}}</td>
                        <td style="text-align:right">{{number_format($pembelian->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Nomor Akun</td>
                        <td style="text-align:right">{{$pembelian->account_number}}</td>
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
                    <td>{{$pembelian->no_transaksi}}</td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>{{\App\Helpers\GlobalHelper::tgl_indo($pembelian->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($pembelian->created_at,'H:i:s')}}</td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td>@if(!empty($pembelian->anggota))({{$pembelian->anggota->no_anggota}}) {{$pembelian->anggota->nama_lengkap}} @else Non Member @endif</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>:</td>
                    <td>{{$pembelian->nama_petugas}}</td>
                </tr>
            </table>
        </div>
        <div class="items">
            <table style="width:100%">
                @foreach ($pembelian->items as $key => $value)
                    <tr>
                        <th colspan="3">{{$value->produk->nama_barang}}</th>
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
                    <td style="text-align:right">{{number_format($pembelian->total_tanpa_diskon,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Total Diskon Barang</td>
                    <td style="text-align:right">{{number_format($pembelian->total_diskon,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align:right">{{number_format($pembelian->subtotal,2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>Diskon Transaksi</td>
                    <td style="text-align:right">{{number_format($pembelian->diskon,2,'.',',')}}</td>
                </tr>
            </table>
        </div>
        <div class="accounting">
            <table style="width:100%">
                <tr>
                    <td>Total</td>
                    <td style="text-align:right">{{number_format($pembelian->total_pembayaran,2,'.',',')}}</td>
                </tr>
                @if($pembelian->fid_metode_pembayaran==1)
                    <tr>
                        <td>Tunai</td>
                        <td style="text-align:right">{{number_format($pembelian->tunai,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Kembali</td>
                        <td style="text-align:right">{{number_format($pembelian->kembali,2,'.',',')}}</td>
                    </tr>
                @elseif($pembelian->fid_metode_pembayaran==3)
                    <tr>
                        <td>Kredit</td>
                        <td style="text-align:right">{{number_format($pembelian->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                @elseif($pembelian->fid_metode_pembayaran==5)
                    <tr>
                        <td>{{$pembelian->metode_pembayaran}}</td>
                        <td style="text-align:right">{{number_format($pembelian->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Nomor Debit Card</td>
                        <td style="text-align:right">{{$pembelian->no_debit_card}}</td>
                    </tr>
                @elseif($pembelian->fid_metode_pembayaran==7)
                    <tr>
                        <td>{{$pembelian->metode_pembayaran}}</td>
                        <td style="text-align:right">{{number_format($pembelian->total_pembayaran,2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td>Nomor Akun</td>
                        <td style="text-align:right">{{$pembelian->account_number}}</td>
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
            if (event.keyCode == 13) window.location.href = "{{ url('pos/pembelian_baru') }}";
        });
    </script>
@endsection
