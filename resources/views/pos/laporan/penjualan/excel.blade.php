{{--@extends('layouts.report2')--}}

{{--@section('content')--}}
    <h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
    <h1 style="text-align: center;margin: 0;">LAPORAN PENJUALAN</h1>
    <p style="margin: 0;text-align: center;">
        @if(($params['tanggal_awal'] ?? '') != '')
            {{ format_date($params['tanggal_awal'] ?? '') }}
        @endif
        @if(($params['tanggal_akhir'] ?? '') != '')
            s/d {{ format_date($params['tanggal_akhir'] ?? '') }}
        @endif
    </p>
    <br>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Anggota</th>
        <th>No. Penjualan</th>
        <th>Tanggal</th>
        <th>Barang</th>
        <th>Kategori</th>
        <th style="text-align:right">Harga</th>
        <th style="text-align:right">Jumlah</th>
        <th style="text-align:right">Subtotal</th>
        <th>Satuan</th>
        <th>Laba</th>
    </tr>
    </thead>
    <tbody>
    @php($temp = '')
    @php($temp2 = '')
    @php($total_before = 0)
    @php($total_before2 = 0)
    @php($sub_diskon = 0)
    @php($total_diskon = 0)
    @php($total_diskon2 = 0)
    @php($sub_margin = 0)
    @php($total_margin = 0)
    @foreach ($penjualan as $key => $value)
        @if($key > 0)
            @if($temp2 != $value->fid_penjualan)
                <tr>
                    <td colspan="7" style="text-align: right;">Diskon</td>
                    <td style="text-align: right;">{{ ($sub_diskon) }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="7" style="text-align: right;">Total Transaksi</td>
                    <td style="text-align: right;">{{ ($total_before2 - $sub_diskon) }}</td>
                    <td></td>
                    <td style="text-align: right;">{{ $sub_margin }}</td>
                </tr>
                @php($total_diskon += $sub_diskon)
                @php($total_diskon2 += $sub_diskon)
                @php($total_margin += $sub_margin)
                @php($sub_diskon = 0)
                @php($sub_margin = 0)
                @php($total_before2 = 0)
            @endif
            @if($temp != $value->penjualan->fid_anggota)
                <tr>
                    <td colspan="7" style="text-align: right;"><b>Sub Diskon</b></td>
                    <td style="text-align: right;"><b>{{ ($total_diskon2) }}</b></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="7" style="text-align: right;"><b>Sub Total</b></td>
                    <td style="text-align: right;"><b>{{ ($total_before - $total_diskon2) }}</b></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="9"><b>{{ $value->penjualan->fid_anggota ?? '' }} - {{ $value->penjualan->anggota->nama_lengkap ?? '' }}</b></td>
                </tr>
                @php($total_before = 0)
                @php($total_diskon2 = 0)
            @endif
        @else
            <tr>
                <td colspan="9"><b>{{ $value->penjualan->fid_anggota ?? '' }} - {{ $value->penjualan->anggota->nama_lengkap ?? '' }}</b></td>
            </tr>
        @endif
        <tr>
            <td></td>
            <td>{{ $value->penjualan->no_transaksi ?? '' }}</td>
            <td>{{ format_date($value->penjualan->created_at ?? '') }}</td>
            <td>
                @if(!empty($value->produk))
                    {{ $value->produk->nama_produk ?? '' }} - {{ $value->produk->kode ?? '' }}
                @else
                    {{ $value->nama_barang }}
                @endif
            </td>
            <td>{{ $value->produk->kategori_produk->nama_kategori ?? '' }}</td>
            <td style="text-align:right">{{ ($value->harga) }}</td>
            <td style="text-align:right">{{ ($value->jumlah) }}</td>
            <td style="text-align:right">{{ ($value->total) }}</td>
            <td>{{ $value->produk->satuan_barang->satuan ?? '' }}</td>
            <td style="text-align:right">{{ ($value->margin_nominal) }}</td>
        </tr>
        @php($temp = $value->penjualan->fid_anggota)
        @php($temp2 = $value->fid_penjualan)
        @php($total_before += $value->total)
        @php($total_before2 += $value->total)
        @php($sub_diskon = $value->penjualan->diskon)
        @php($sub_margin += $value->margin_nominal)
    @endforeach
    <tr>
        <td colspan="7" style="text-align: right;">Diskon</td>
        <td style="text-align: right;">{{ ($sub_diskon) }}</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: right;">Total Transaksi</td>
        <td style="text-align: right;">{{ ($total_before2 - $sub_diskon) }}</td>
        <td></td>
        <td style="text-align: right;">{{ $sub_margin }}</td>
    </tr>
    @php($total_diskon += $sub_diskon)
    @php($total_diskon2 += $sub_diskon)
    <tr>
        <td colspan="7" style="text-align: right;"><b>Sub Diskon</b></td>
        <td style="text-align: right;"><b>{{ ($total_diskon2) }}</b></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: right;"><b>Sub Total</b></td>
        <td style="text-align: right;"><b>{{ ($total_before - $total_diskon2) }}</b></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: right;"><b>Total</b></td>
        <td style="text-align: right;"><b>{{ ($penjualan->sum('total') - $total_diskon) }}</b></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: right;"><b>Total Laba</b></td>
        <td style="text-align: right;"><b>{{ ($total_margin) }}</b></td>
        <td></td>
        <td></td>
    </tr>
    </tbody>
</table>

{{--@endsection--}}
