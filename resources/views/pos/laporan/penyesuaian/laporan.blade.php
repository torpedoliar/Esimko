@extends('layouts.report2')

@section('content')
    <h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
    <h1 style="text-align: center;margin: 0;">LAPORAN PENYESUAIAN STOK</h1>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Tanggal</th>
        <th>Kode Barang</th>
        <th>Nama Barang</th>
        <th class="center">Jumlah</th>
        <th class="center">Jumlah * Harga</th>
        <th class="center">Jenis</th>
        <th>Keterangan</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($penyesuaian as $key => $value)
        <tr>
            <td style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
            <td>{{$value->produk->kode ?? ''}}</td>
            <td>{{$value->produk->nama_produk ?? ''}}</td>
            <td class="center">{{$value->jumlah}}</td>
            <td class="center">{{$value->jumlah * $value->hpp}}</td>
            <td class="center">{{$value->jenis}}</td>
            <td>{{$value->keterangan}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
