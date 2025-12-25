@php
  $subpage='Pilih Produk';
  $keterangan='Silahkan melihat dan memilih produk yang dijual di toko kami';
@endphp
@extends('main.belanja.layout')
@section('add_css')
  .input-group-text{
    background: #fff
  }
  .jumlah .form-control{
    border-right:none !important
  }
@endsection
@section('content_belanja')
<div class="card m-0">
  <div class="card-body">
    <div class="row mb-2">
      <div class="col-auto">
        <div class="produk-wrapper m-0" style="height:300px;width:300px">
          <img class="img-thumbnail" src="{{(!empty($data['produk']->foto) ? asset('storage/'.$data['produk']->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
        </div>
      </div>
      <div class="col">
        Kode. {{$data['produk']->kode}}
        <h5 class="mt-2 mb-3 font-size-20">{{$data['produk']->nama_produk}}</h5>
        <hr style="margin-top: 0.8rem;margin-bottom: 0.8rem;">
        <div class="row">
          <div class="col-4">
            <div>
              <label class="text-muted text-truncate mb-1">Harga Satuan</label>
              <h5 class="mb-0 font-size-14">Rp {{number_format($data['produk']->harga_jual,0,',','.')}} / {{$data['produk']->satuan}}</h5>
            </div>
          </div>
          <div class="col-4">
            <div>
              <label class="text-muted text-truncate mb-1">Terjual</label>
              <h5 class="mb-0 font-size-14">{{$data['produk']->terjual}} {{$data['produk']->satuan}}</h5>
            </div>
          </div>
          <div class="col-4">
            <div>
              <label class="text-muted text-truncate mb-1">Stok</label>
              <h5 class="mb-0 font-size-14">{{$data['produk']->sisa}} {{$data['produk']->satuan}}</h5>
            </div>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-4">
            <div>
              <label class="text-muted text-truncate mb-1">Golongan Barang</label>
              <h5 class="mb-0 font-size-14">{{$data['produk']->kelompok}}</h5>
            </div>
          </div>
          <div class="col-4">
            <div>
              <label class="text-muted text-truncate mb-1">Departemen</label>
              <h5 class="mb-0 font-size-14">{{$data['produk']->kategori}}</h5>
            </div>
          </div>
          <div class="col-4">
            <div>
              <label class="text-muted text-truncate mb-1">Sub Departemen</label>
              <h5 class="mb-0 font-size-14">{{$data['produk']->sub_kategori}}</h5>
            </div>
          </div>
        </div>
        <hr style="margin-top: 0.8rem;margin-bottom: 0.8rem;">
        <label class="text-muted text-truncate mb-1">Deskripsi Produk</label>
        <div>{{(!empty($data['produk']->deskripsi) ? $data['produk']->deskripsi : 'Tidak Ada Dekripsi')}}</div>
      </div>
    </div>
  </div>
</div>
<form action="{{url('main/belanja/produk/proses')}}" method="post" style="position:sticky;bottom:0;width:100%;z-index:100;" id="test" >
  {{ csrf_field() }}
  <div class="card">
    <div class="card-body cart-footer" id="cart_footer" >
      <div class="row">
        <div class="col-md-5"></div>
        <div class="col-md-3">
          <div class="jumlah">
            <input data-toggle="touchspin" style="text-align:right" onchange="calc_belanja()" id="jumlah" name="jumlah" type="text" value="1" data-max="{{$data['produk']->sisa}}" data-bts-postfix="{{$data['produk']->satuan}}">
            <div class="mb-0" style="text-align:right;margin-top:10px">
              <div class="text-muted text-truncate font-size-12">Total Harga</div>
              <h5 id="total_harga"></h5>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-success btn-block" name="action" value="add_cart">Masukkan Keranjang</button>
          <button class="btn btn-primary btn-block" name="action" value="buy_now" >Beli Sekarang</button>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" name="id" value="{{$id}}">
  <input type="hidden" name="harga" value="{{$data['produk']->harga_jual}}">
</form>
@endsection
@section('add_js')
<script>
calc_belanja();
function calc_belanja(){
  jumlah=$('#jumlah').val();
  harga_satuan={{$data['produk']->harga_jual}};
  total_harga=harga_satuan*jumlah;
  $('#total_harga').html('Rp '+accounting.formatNumber(total_harga,0,'.',','));
}
</script>
@endsection
