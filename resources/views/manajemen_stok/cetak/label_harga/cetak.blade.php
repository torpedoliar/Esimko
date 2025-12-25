@extends('layouts.report')
@section('css')
  <style>
  .label-container {
    display: flex;
    flex-wrap: wrap;
  }

  .label-container .label {
    border:1px solid #363636;
    border-top:8px solid #363636;
    border-bottom:8px solid #363636;
    width: 300px;
    margin: 4px;
  }
  .label-container .label .title{
    padding:8px;
    border-bottom: 1px solid #363636;
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
    white-space: normal;
    -webkit-line-clamp: 2;
    height:32px;
    line-height:18px;
  }
  .label-container .label .price{
    padding:10px;
    border-bottom: 1px solid #363636;
    font-size:45px;
    font-weight:600;
    text-align:right;
    position: relative;
  }
  .label-container .label .footer{
    height:55px;
    position: relative;
  }
  </style>
@endsection
@section('content')
  <div class="label-container">
    @foreach ($data['produk'] as $key => $value)
      @for ($i=1; $i <= $value->jumlah ; $i++)
        <div class="label">
          <div class="title">{{$value->nama_produk}}</div>
          <div class="price">
            <div style="position:absolute;font-size:20px;top:5px;left:5px;font-weight:400">Rp</div>
            {{number_format($value->harga_jual,0,',','.')}}
            <div style="position:absolute;font-size:14px;bottom:5px;left:5px;font-weight:300">{{$value->kode}}</div>
          </div>
          <div class="footer">
            <div style="position:absolute;font-size:12px;top:5px;left:5px;font-weight:300">{{$value->satuan}}</div>
            <div style="position:absolute;font-size:12px;top:5px;right:5px;font-weight:300">{{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}</div>
            <div style="position:absolute;font-size:12px;bottom:5px;right:5px;font-weight:300">
              {{(!empty($value->sub_kategori) ? $value->sub_kategori : $value->kategori)}}
            </div>
          </div>
        </div>
      @endfor
    @endforeach
  </div>

@endsection
