{{--@extends('layouts.report2')--}}

{{--@section('content')--}}
<h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
<h1 style="text-align: center;margin: 0;">LAPORAN SALDO SIMPANAN {{ $request->jenis }}</h1>
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
        <th>Tanggal</th>
        <th>No. Anggota</th>
        <th>Anggota</th>
        <th style="text-align:right;width:150px">Nominal</th>
        <th>Status</th>
        <th>Operator</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $key => $value)
        <tr>
            <td>{{ format_date($value->tanggal) }}</td>
            <td>{{ $value->anggota->no_anggota }}</td>
            <td>{{ $value->anggota->nama_lengkap }}</td>
            <td style="text-align:right">Rp {{ format_number($value->nominal) }}</td>
            <td>{{ $value->status->status }}</td>
            <td>{{ $value->operator->nama_lengkap }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{--@endsection--}}
