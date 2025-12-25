@php
  $subpage='Belanja Toko';
  $keterangan='Halaman checkout belanja toko anggota';
@endphp
@extends('main.belanja.layout')
@section('content_belanja')
<form action="{{url('main/belanja/checkout/proses')}}" method="post" id="proses_lanjutkan">
  {{ csrf_field() }}
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>No. Transaksi</label>
            <input type="text" class="form-control" name="no_transaksi" value="{{(!empty($data['penjualan']) ? $data['penjualan']->no_transaksi : null)}}" readonly>
          </div>
        </div>
      </div>
      <table class="table table-middle table-hover">
        <thead>
          <tr>
            <th>Nama Barang</th>
            <th class="center">Jumlah</th>
            <th style="text-align:right;width:135px">Harga</th>
            <th style="text-align:right;width:135px">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data['items'] as $key => $value)
          <tr>
            <td>
              <div class="media">
                <div class="rounded mr-3 produk-wrapper m-0" style="height:40px;width:40px">
                  <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                </div>
                <div class="align-self-center media-body">
                  <span>Kode. {{$value->kode}}</span>
                  <h6>{{$value->nama_produk}}</h6>
                </div>
              </div>
            </td>
            <td class="center">{{$value->jumlah}} {{$value->satuan}}</td>
            <td style="text-align:right;width:135px" >{{number_format($value->harga,0,',','.')}}  </td>
            <td style="text-align:right;width:135px" >
              <h6 style="font-weight:600" id="subtotal_{{$value->id}}">{{number_format($value->total,0,',','.')}}</h6>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="card" style="position:sticky;bottom:0;width:100%;z-index:100">
    <div class="card-body cart-footer" id="cart_footer" >
      <div class="row">
        <div class="col-md-6">
          <div style="display:flex">
            <div style="font-size:15px;text-align:right;margin-right:10px;align-self: center!important;">Total ({{$data['penjualan']->jumlah}} Produk)</div>
            <div style="font-size:25px;font-weight:600;color:#409e7c;align-self: center!important;">Rp {{number_format($data['penjualan']->total,0,',','.')}}</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="pull-right">
            <input type="hidden" name="total_pembayaran" value="{{$data['penjualan']->total}}">
            <input type="hidden" name="id" value="{{$id}}">
            <button class="btn btn-danger" type="button" onclick="confirm_checkout('batalkan')">Batalkan</button>
            <button class="btn btn-primary" type="button" onclick="confirm_checkout('lanjutkan')">Lanjutkan</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
<form action="{{url('main/belanja/checkout/proses_pembatalan')}}" method="post" id="proses_pembatalan">
  {{ csrf_field()}}
  <input type="hidden" name="id" value="{{$id}}">
  <input type="hidden" name="status" id="status">
</form>
@endsection
@section('add_js')
<script>
function confirm_checkout(action){
  if(action=='batalkan'){
    text='Apakah anda yakin ingin mebatalkan transaksi ini';
  }
  else{
    text='Apakah anda yakin ingin melankutkan transaksi ini';
  }
  Swal.fire({
    title: "Are you sure?",
    text:text ,
    type:"question",
    showCancelButton: true,
    confirmButtonColor: '#d63030',
    cancelButtonColor: '#cbcbcb',
    confirmButtonText: 'Yes'
  }).then((result) => {
    if (result.value == true) {
      if(action=='batalkan'){
        $('#status').val('3');
        $('#proses_pembatalan').submit();
      }
      else{
        $('#proses_lanjutkan').submit();
      }
    }
  });
}
</script>
@endsection
