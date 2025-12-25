@php
  $subpage='Belanja Toko';
@endphp
@extends('main.belanja.layout')
@section('content_belanja')
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-auto">
        <div class="produk-wrapper" style="height:330px;width:330px">
          <img class="img-thumbnail" src="{{(!empty($data['produk']->foto) ? asset('storage/'.$data['produk']->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
        </div>
      </div>
      <div class="col">
        <form action="{{url('belanja/produk/proses')}}" method="post" >
          {{ csrf_field() }}
          <span style="background:#e6e6e6;padding:3px 8px;border-radius:3px">{{$data['produk']->nama_kategori}}</span>
          <span style="font-size:14px;font-weight:500;margin-left:10px">Kode. {{$data['produk']->kode}}</span>
          <h5 class="mt-3 mb-3 font-size-17">{{$data['produk']->nama_produk}}</h5>
          <hr style="margin-top: 0.8rem;margin-bottom: 0.8rem;">
          <div class="row">
            <div class="col-6">
              <div>
                <p class="text-muted text-truncate mb-2">Harga Satuan</p>
                <h5 class="mb-0">Rp {{number_format($data['produk']->harga_satuan,0,',','.')}}</h5>
              </div>
            </div>
            <div class="col-3">
              <div>
                <p class="text-muted text-truncate mb-2">Terjual</p>
                <h5 class="mb-0">{{$data['produk']->terjual}} {{$data['produk']->satuan}}</h5>
              </div>
            </div>
            <div class="col-3">
              <div>
                <p class="text-muted text-truncate mb-2">Stok</p>
                <h5 class="mb-0">{{$data['produk']->sisa}} {{$data['produk']->satuan}}</h5>
              </div>
            </div>
          </div>
          <hr style="margin-top: 0.8rem;margin-bottom: 0.8rem;">
          <div class="row mt-3">
            <div class="col-md-6">
              <div class="form-group">
                <label>Jumlah</label>
                <input data-toggle="touchspin" onchange="calc_belanja()" id="jumlah" name="jumlah" type="text" value="1" data-max="{{$data['produk']->sisa}}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Total Harga</label>
                <h5 class="mb-0" id="total_harga"></h5>
              </div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <button class="btn btn-outline-success btn-block" name="action" value="add_cart">Masukkan Keranjang</button>
            </div>
            <div class="col-md-6">
              <button class="btn btn-primary btn-block" name="action" value="buy_now" >Beli Sekarang</button>
            </div>
          </div>
          <input type="hidden" name="id" value="{{$id}}">
          <input type="hidden" name="harga" value="{{$data['produk']->harga_satuan}}">
        </form>
      </div>
    </div>
    <h5 class="mb-2">Deskripsi Produk</h5>
    {{(!empty($data['produk']->deskripsi) ? $data['produk']->deskripsi : 'Tidak Ada Dekripsi')}}
    <h5 class="mb-3 mt-5">Produk Terkait</h5>
    <div class="row">
      @foreach ($data['produk-terkait'] as $key => $value)
      <div class="col-xl-3 col-sm-4 col-6">
        <a href="{{url('belanja/produk/detail?id='.$value->kode)}}">
          <div class="card m-0">
            <div class="produk" style="border:1px solid #e5e5e5">
              <img class="card-img-top img-fluid" src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}">
              <div class="card-body">
                <h6><a href="" class="text-secondary">{{$value->nama_produk}}</a></h6>
                <div class="mt-3">
                  <span class="discount">20%</span>
                  <span class="text-muted font-size-10"><del>Rp 50.000</del></span>
                </div>
                <h6 class="price mt-2">Rp {{number_format($value->harga_satuan,0,',','.')}}</h6>
              </div>
            </div>
          </div>
        </a>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
@section('add_js')
<script>
calc_belanja();
function calc_belanja(){
  jumlah=$('#jumlah').val();
  harga_satuan={{$data['produk']->harga_satuan}};
  total_harga=harga_satuan*jumlah;
  $('#total_harga').html('Rp '+accounting.formatNumber(total_harga,0,'.',','));
}
</script>
@endsection
