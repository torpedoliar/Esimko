@extends('layouts.report2')

@section('content')
<h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
<h1 style="text-align: center;margin: 0;">LAPORAN RETUR PEMBELIAN</h1>
<p style="margin: 0;text-align: center;">
    @if(($request->tanggal_awal ?? '') != '')
        {{ format_date($request->tanggal_awal) }}
    @endif
    @if(($request->tanggal_akhir ?? '') != '')
        s/d {{ format_date($request->tanggal_akhir) }}
    @endif
</p>
<br>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No. Retur Pembelian</th>
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
    @foreach ($retur_pembelian as $key => $value)
        @if($temp != $value->retur_pembelian->fid_supplier)
            @if($total_before > 0)
                <tr>
                    <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
                    <td style="text-align: right;"><b>{{ ($total_before) }}</b></td>
                    <td></td>
                </tr>
            @endif
            <tr>
                <td colspan="8"><b>{{ $value->retur_pembelian->supplier->nama_supplier ?? '' }}</b></td>
            </tr>
            @php($total_before = 0)
        @endif
        <tr>
            <td>{{ $value->retur_pembelian->no_retur ?? '' }}</td>
            <td>{{ format_date($value->retur_pembelian->created_at ?? '') }}</td>
            <td>{{ $value->produk->nama_produk }} - {{ $value->produk->kode }}</td>
            <td>{{ $value->produk->kategori_produk->nama_kategori ?? '' }}</td>
            <td style="text-align:right">{{ ($value->harga) }}</td>
            <td style="text-align:right">{{ ($value->jumlah) }}</td>
            <td style="text-align:right">{{ ($value->total) }}</td>
            <td>{{ $value->produk->satuan_barang->satuan }}</td>
        </tr>
        @php($temp = $value->retur_pembelian->fid_supplier)
        @php($total_before += $value->total)
    @endforeach
    <tr>
        <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
        <td style="text-align: right;"><b>{{ ($total_before) }}</b></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6" style="text-align: right;"><b>Total</b></td>
        <td style="text-align: right;"><b>{{ ($retur_pembelian->sum('total')) }}</b></td>
        <td></td>
    </tr>
    </tbody>
</table>
@endsection
