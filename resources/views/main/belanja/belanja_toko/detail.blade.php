@php
  $subpage='Riwayat Belanja';
@endphp
@extends('main.belanja.layout')
@section('content_belanja')
<div class="card">
  <div class="card-body">
    <div class="center mb-5">
      <img src="{{asset('assets/images/'.$data['penjualan']->icon)}}" style="width:80px">
      <h4 class="mt-3">{{$data['keterangan']->label}}</h4>
      <p>{{$data['keterangan']->keterangan}}</p>
    </div>
    <div class="row mt-3">
      <div class="col-md-7">
        <table style="width:100%">
          <tr>
            <td width="110px">Waktu</td>
            <td width="10px">:</td>
            <td style="text-align:left">{{\App\Helpers\GlobalHelper::tgl_indo($data['penjualan']->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($data['penjualan']->created_at,'H:i:s')}}</td>
          </tr>
          <tr>
            <td>No. Transaksi</td>
            <td>:</td>
            <td style="text-align:left">{{$data['penjualan']->no_transaksi}}</td>
          </tr>
          <tr>
            <td>Jenis Belanja</td>
            <td>:</td>
            <td style="text-align:left">Belanja Toko</td>
          </tr>
        </table>
      </div>
      <div class="col-md-5">
        <table style="width:100%">
          <tr>
            <td width="170px">Metode Pembayaran</td>
            <td width="10px">:</td>
            <td style="text-align:left">{{$data['penjualan']->metode_pembayaran}}</td>
          </tr>
          <tr>
            <td>Jumlah Barang</td>
            <td>:</td>
            <td style="text-align:left">{{$data['penjualan']->jumlah}}</td>
          </tr>
          <tr>
            <td>Total Belanja</td>
            <td>:</td>
            <td style="text-align:left">Rp {{number_format($data['penjualan']->total)}}</td>
          </tr>
        </table>
      </div>
    </div>
    <table class="table table-middle table-hover mt-2">
      <thead>
        <tr>
          <th>Nama Barang</th>
          <th class="center">Jumlah</th>
          <th class="center">Harga Satuan</th>
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
          <td style="text-align:right;width:135px" >Rp {{number_format($value->harga,0,',','.')}}</td>
          <td style="text-align:right;width:135px" >Rp {{number_format($value->total,0,',','.')}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <div class="pull-right">
      <button class="btn btn-secondary" type="button" onclick="batalkan(1)">Kembali</button>
      <button class="btn btn-danger" type="button" onclick="batalkan(4)">Batalkan Transaksi</button>
    </div>
  </div>
</div>
<form action="{{url('belanja/checkout/proses_pembatalan')}}" method="post" id="proses_pembatalan">
  {{ csrf_field()}}
  <input type="hidden" name="id" value="{{$id}}">
  <input type="hidden" name="status" id="status">
</form>
@endsection
@section('add_js')
<script>
function batalkan(jenis){
  if(jenis==1){
    $('#status').val(jenis);
    $('#proses_pembatalan').submit();
  }
  else{
    Swal.fire({
      title: "Are you sure?",
      text: 'Apakah anda yakin ingin mebatalkan transaksi ini',
      type:"question",
      showCancelButton: true,
      confirmButtonColor: '#d63030',
      cancelButtonColor: '#cbcbcb',
      confirmButtonText: 'Yes'
    }).then((result) => {
      if (result.value == true) {
        $('#status').val(status);
        $('#proses_pembatalan').submit();
      }
    });
  }
}
</script>
@endsection
