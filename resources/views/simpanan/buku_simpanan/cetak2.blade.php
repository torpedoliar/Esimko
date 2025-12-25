@extends('layouts.report')
@section('title')
  Cetak Buku Simpanan
@endsection
@section('css')

@endsection
@section('content')
<table style="width:13.8cm;margin-top:15cm;margin-left:0.2cm;font-size:12px">

  <tbody>
    {{-- @if(empty($request->page) || $request->page==1)
    @for ($i=0; $i < $request->spacing; $i++)
      <tr>
        <td colspan="7" style="text-align:center;color:#fff">Row Spacing</td>
      </tr>
    @endfor
    @endif --}}

    @foreach ($data['data'] as $key => $value)

      @if($value->is_cetak == 1)
      <tr>

      </tr>
      @else
      @php
            $abbreviation = explode(' ', trim($value->operator ))[0];
      @endphp


      <tr>
        <td style="text-align:center;width:1.8cm;font-weight:bold">{{ $key+1 }}</td>
        <td style="text-align:right;width:1.8cm;font-weight:bold">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
        <td style="text-align:right;width:2.6cm;font-weight:bold">{{$value->sandi}}</td>
        <td style="text-align:right;width:1.9cm;font-weight:bold">&nbsp;&nbsp;&nbsp;&nbsp;{{number_format($value->debit,'0',',','.')}}</td>
        <td style="text-align:right;width:1.9cm;font-weight:bold">&nbsp;&nbsp;&nbsp;&nbsp;{{number_format($value->kredit,'0',',','.')}}</td>
        <td style="text-align:right;width:1.9cm;font-weight:bold">{{number_format($value->saldo,'0',',','.')}}</td>
        <td style="text-align:right;width:0.8;font-weight:bold">&nbsp;&nbsp;&nbsp;asd</td>
        </tr>
      @endif
    @endforeach
  </tbody>
</table>
@endsection
