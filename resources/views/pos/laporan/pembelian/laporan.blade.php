@extends('layouts.report2')

@section('content')
<h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
<h1 style="text-align: center;margin: 0;">LAPORAN PEMBELIAN</h1>
<p style="margin: 0;text-align: center;">
    @if(($request->tanggal_awal ?? '') != '')
        {{ format_date($request->tanggal_awal) }}
    @endif
    @if(($request->tanggal_akhir ?? '') != '')
        s/d {{ format_date($request->tanggal_akhir) }}
    @endif
</p>
<br>
<table class="table table-middle table-custom">
    <thead>
    <tr>
        <th>No. Pembelian</th>
        <th>Tanggal</th>
        <th>Barang</th>
        <th>Kategori</th>
        <th style="text-align:right">Harga</th>
        <th style="text-align:right">Jumlah</th>
        <th style="text-align:right">Subtotal</th>
        <th>Satuan</th>
    </tr>
    </thead>
    <tbody>
    @php($temp = '')
    @php($total_before = 0)
    @php($sub_diskon = 0)
    @php($sub_ppn = 0)
    @php($sub_biaya = 0)
    @php($total_diskon = 0)
    @php($total_ppn = 0)
    @php($total_biaya = 0)
    @foreach ($pembelian as $key => $value)
        @if($temp != $value->pembelian->fid_supplier)
            @if($total_before > 0)
                <tr>
                    <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
                    <td style="text-align: right;"><b>{{ format_number($total_before - $sub_diskon + $sub_ppn) }}</b></td>
                    <td></td>
                </tr>
                @php($total_diskon += $sub_diskon)
                @php($total_ppn += $sub_ppn)
                @php($total_biaya += $sub_biaya)
            @endif
            <tr>
                <td colspan="8"><b>{{ $value->pembelian->supplier->nama_supplier ?? '' }}</b></td>
            </tr>
            @php($total_before = 0)
        @endif
        <tr>
            <td>{{ $value->pembelian->no_pembelian ?? '' }}</td>
            <td>{{ format_date($value->pembelian->created_at ?? '') }}</td>
            <td>{{ $value->produk->nama_produk ?? '' }} - {{ $value->produk->kode ?? '' }}</td>
            <td>{{ $value->produk->kategori_produk->nama_kategori ?? '' }}</td>
            <td style="text-align:right">{{ format_number($value->harga) }}</td>
            <td style="text-align:right">{{ format_number($value->jumlah) }}</td>
            <td style="text-align:right">{{ format_number($value->total) }}</td>
            <td>{{ $value->produk->satuan_barang->satuan ?? '' }}</td>
        </tr>
        @php($temp = $value->pembelian->fid_supplier)
        @php($total_before += $value->total)
        @php($sub_diskon = $value->pembelian->diskon_nominal)
        @php($sub_ppn = $value->pembelian->ppn_nominal)
        @php($sub_biaya = $value->pembelian->biaya_tambahan)
    @endforeach
    <tr>
        <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
        <td style="text-align: right;"><b>{{ format_number($total_before - $sub_diskon + $sub_ppn + $sub_biaya) }}</b></td>
        <td></td>
    </tr>
    @php($total_diskon += $sub_diskon)
    @php($total_ppn += $sub_ppn)
    @php($total_biaya += $sub_biaya)
    <tr>
        <td colspan="6"><b>TOTAL</b></td>
        <td style="text-align:right"><b>{{ format_number($pembelian->sum('total') - $total_diskon + $total_ppn + $total_biaya) }}</b></td>
        <td></td>
    </tr>
    </tbody>
</table>
@endsection
