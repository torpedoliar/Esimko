@extends('layouts.report2')

@section('content')
<h2># Pembelian</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No.Pembelian</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($pembelian as $key => $value)
        <tr>
            <td>{{ $value->pembelian->no_pembelian }}</td>
            <td>{{ $value->pembelian->tanggal}}</td>
            <td class="center">{{ $value->jumlah }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2">Total</td>
        <td>{{ $pembelian->sum('jumlah') }}</td>
    </tr>
    </tbody>
</table>
<h2 class="mt-3"># Retur Pembelian</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No. Retur Pembelian</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($retur_pembelian as $key => $value)
        <tr>
            <td>{{ $value->retur_pembelian->no_retur }}</td>
            <td>{{ $value->retur_pembelian->tanggal}}</td>
            <td class="center">{{ $value->jumlah }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2">Total</td>
        <td>{{ $retur_pembelian->sum('jumlah') }}</td>
    </tr>
    </tbody>
</table>
<h2># Penjualan</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No.Penjualan</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($penjualan as $key => $value)
        <tr>
            <td>{{ $value->penjualan->no_transaksi }}</td>
            <td>{{ $value->penjualan->tanggal}}</td>
            <td class="center">{{ $value->jumlah }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2">Total</td>
        <td>{{ $penjualan->sum('jumlah') }}</td>
    </tr>
    </tbody>
</table>
<h2 class="mt-3"># Retur Penjualan</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>No. Retur Penjualan</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($retur_penjualan as $key => $value)
        <tr>
            <td>{{ $value->retur_penjualan->no_retur }}</td>
            <td>{{ $value->retur_penjualan->tanggal }}</td>
            <td class="center">{{ $value->jumlah }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2">Total</td>
        <td>{{ $retur_penjualan->sum('jumlah') }}</td>
    </tr>
    </tbody>
</table>
<h2 class="mt-3"># Penyesuaian Stok</h2>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Keterangan</th>
        <th>Tanggal</th>
        <th class="center">Jumlah</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($opname as $key => $value)
        <tr>
            <td>{{ $value->keterangan }}</td>
            <td>{{ $value->tanggal}}</td>
            <td class="center">{{ $value->jumlah }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2">Total</td>
        <td>{{ $opname->sum('jumlah') }}</td>
    </tr>
    </tbody>
</table>
@endsection
