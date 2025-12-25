@php
  $subpage='Belanja Toko';
@endphp
@extends('main.belanja.layout')
@section('content_belanja')
<form action="{{url('belanja/checkout/proses')}}" method="post">
  {{ csrf_field() }}
  <div class="card  m-0">
    <div class="card-body">
      <div class="row">
        <div class="col-md-5">
          <div class="form-group">
            <label>No. Transaksi</label>
            <input type="text" class="form-control" name="no_transaksi" value="{{(!empty($data['penjualan']) ? $data['penjualan']->no_transaksi : null)}}" readonly>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>Metode Pembayaran</label>
            <select class="form-control select2" name="metode_pembayaran" id="metode_pembayaran" >
              @foreach ($data['metode-pembayaran'] as $key => $value)
              <option value="{{$value->id}}" {{(!empty($data['penjualan']) && $data['penjualan']->fid_metode_pembayaran == $value->id ? 'selected' : '' )}} >{{$value->metode_pembayaran}}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <table class="table table-middle table-hover">
        <thead>
          <tr>
            <th>Nama Barang</th>
            <th class="center">Jumlah</th>
            <th style="text-align:right;width:135px">Harga Satuan</th>
            <th style="text-align:right;width:135px">Subtotal</th>
            <th style="width:50px"></th>
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
            <td style="width:50px">
              <div class="text-center">
                <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="card" style="position:sticky;bottom:0;width:100%;z-index:10000">
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
            {{-- <button class="btn btn-secondary" type="button" onclick="batalkan()">Tambahkan Barang</button> --}}
            <button class="btn btn-primary">Lanjutkan Transaksi</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection
@section('add_js')
<script>
$('#metode_pembayaran').on('change', function() {
  id=this.value;
  $.get("{{ url('api/find_metode_pembayaran') }}/"+id,function(result){
    if(result.group=='cash'){
      $('#form_cash').show();
      $('#form_debit').hide();
      $('#form_kredit').hide();
    }
    else if(result.group=='kredit'){
      $('#form_cash').hide();
      $('#form_debit').hide();
      $('#form_kredit').show();
    }
    else{
      $('#form_cash').hide();
      $('#form_debit').show();
      $('#form_kredit').hide();
    }
  });
});
</script>
@endsection
