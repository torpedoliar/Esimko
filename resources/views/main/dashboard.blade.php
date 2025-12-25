@php
  $page='Dashboard';
  $subpage='Dashboard';
@endphp
@section('title')
Dashboard |
@endsection
@section('css')
  <style>
  .nav-pills>li>a, .nav-tabs>li>a {
    color: #2f2f2f;
    font-weight: 400;
  }
  .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
    color: #fff;
    background-color: #1a4f73;
  }
  .nav-pills .nav-link {
    border-radius: 0px;
  }
  .card-title {
    font-size: 15px;
    margin: 0px;
    font-weight: 500;
    letter-spacing: 0.5px
  }
  .verti-timeline .event-list {
    position: relative;
    padding: 0 0 0px 20px;
  }
  .table-hover tr td{
    cursor: pointer;
  }
  .list-berita{
    padding:20px;
    border-bottom: 1px solid #e6e6e6;
    display: block
  }
  .list-berita:hover h5{
    color:#429d9c
  }
  .list-berita .produk-wrapper{
    margin:0px
  }
  </style>
@endsection
@extends('layouts.main')
@section('content')
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-3">
            <div class="media">
              <img src="{{asset('assets/images/icon-page/wallet.png')}}" style="height:70px;margin-right:10px">
              <div class="media-body align-self-center">
                <p class="text-muted mb-1">Saldo Simpanan</p>
                <h5 class="font-size-17">Rp {{number_format($data['saldo-simpanan'],0,',','.')}}</h5>
              </div>
            </div>
          </div>
          <div class="col-3">
            <div class="media">
              <img src="{{asset('assets/images/icon-page/save-money.png')}}" style="height:70px;margin-right:10px">
              <div class="media-body align-self-center">
                <p class="text-muted mb-1">Sisa Pinjaman</p>
                <h5 class="font-size-17">Rp {{number_format(str_replace('-','',$data['sisa-pinjaman']),0,',','.')}}</h5>
              </div>
            </div>
          </div>
          <div class="col-3">
            <div class="media">
              <img src="{{asset('assets/images/icon-page/pay-day.png')}}" style="height:70px;margin-right:10px">
              <div class="media-body align-self-center">
                <p class="text-muted mb-1">Total Angsuran</p>
                <h5 class="font-size-17">Rp {{number_format($data['total-angsuran'],0,',','.')}}</h5>
              </div>
            </div>
          </div>
          <div class="col-3">
            <div class="media">
              <img src="{{asset('assets/images/icon-page/shopping-basket.png')}}" style="height:70px;margin-right:10px">
              <div class="media-body align-self-center">
                <p class="text-muted mb-1">Total Kredit Belanja</p>
                <h5 class="font-size-17">Rp {{number_format($data['total-angsuran-belanja'],0,',','.')}}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      @php
        $jenis=array('simpanan'=>'Simpanan','pinjaman'=>'Pinjaman');
      @endphp
      <div class="col-md-6">
        <div class="card">
          <div class="card-body p-0">
            <div class="p-3">
              <h4 class="card-title">Transaksi Terakhir</h4>
            </div>
            <ul class="nav nav-pills" style="background:#f2f2f5" role="tablist">
              @foreach ($jenis as $key => $value)
              <li class="nav-item">
                <a class="nav-link {{($key=='simpanan' ? 'active' : '')}}" data-toggle="tab" href="#{{$key}}" role="tab">{{$value}}</a>
              </li>
              @endforeach
            </ul>
            <div class="table-responsive" data-simplebar style="height:300px;">
              <div class="tab-content">
                @foreach ($jenis as $key => $value)
                  <div class="tab-pane {{($key=='simpanan' ? 'active' : '')}}" id="{{$key}}" role="tabpanel">
                    @if(count($data['transaksi-terakhir'][$key])==0)
                    <div style="width:100%;text-align:center">
                      <img src="{{asset('assets/images/icon-page/proses.png')}}" class="mt-5" style="width:80px">
                      <p class="font-size-14 mt-3">Tidak ada Transkasi</p>
                    </div>
                    @else
                    <table class="table table-middle table-hover">
                      <tbody>
                        @foreach ($data['transaksi-terakhir'][$key] as $key2 => $value2)
                          <tr onclick="location.href = '{{url('main/'.$key.'/detail?id='.$value2->id)}}'">
                            <td>
                              <h6>{{$value2->jenis_transaksi}}</h6>
                              <p class="text-muted mb-0">{{\App\Helpers\GlobalHelper::tgl_indo($value2->tanggal)}}</p>
                            </td>
                            <td style="text-align:right;">
                              <h6 class="text-truncate">Rp {{number_format($value2->nominal,0,',','.')}}</h6>
                              <span style="color:{{$value2->color}}">{{$value2->status}}</span>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body p-0">
            <div class="p-3">
              <h4 class="card-title">Berita dan Informasi</h4>
            </div>
            <div data-simplebar style="height:335px;">
              @foreach ($data['berita'] as $key => $value)
              <a class="list-berita" href="{{url('main/berita/detail?id='.$value->id)}}">
                <div class="media">
                  <div class="rounded produk-wrapper mr-3" style="height:100px;width:100px">
                    <img src="{{(!empty($value->gambar) ? asset('storage/'.$value->gambar) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                  </div>
                  <div class="media-body align-self-center">
                    <h5 class="mb-2 font-size-16">{{$value->judul}}</h5>
                    <p class="text-muted">{{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,"H:i:s")}}</p>
                  </div>
                </div>
              </a>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
