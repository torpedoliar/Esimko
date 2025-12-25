@php
  $subpage='Keranjang';
  $keterangan='Halaman keranjang belanja anggota sebelum dilakukan proses checkout belanja';
@endphp
@extends('main.belanja.layout')
@section('content_belanja')
<div class="card m-0">
  <div class="card-body">
    <form action="{{url('main/belanja/keranjang/proses')}}" method="post" id="proses_keranjang" >
      {{ csrf_field() }}
      <table class="table table-middle table-hover">
        <thead class="thead-light">
          <tr>
            <th style="width:1px;white-space:nowrap">
              <input type="checkbox" id="check_all">
            </th>
            <th>Nama Barang</th>
            <th style="text-align:right;white-space:nowrap">Harga Satuan</th>
            <th class="center" style="width:150px">Jumlah</th>
            <th style="text-align:right;white-space:nowrap">Total Harga</th>
            <th style="width:50px"></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data['keranjang'] as $key => $value)
          <tr>
            <td><input type="checkbox" class="pilih_barang" name="pilih[]" value="{{$value->id}}" onclick="calc_belanja()"></td>
            <td onclick="location.href='{{url('main/belanja/produk/detail?id='.$value->kode)}}'" style="cursor:pointer">
              <div class="media">
                <div class="rounded mr-3 produk-wrapper m-0" style="height:45px;width:45px">
                  <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                </div>
                <div class="align-self-center media-body">
                  <span>Kode. {{$value->kode}}</span>
                  <h6>{{$value->nama_produk}}</h6>
                </div>
              </div>
            </td>
            <td style="text-align:right;white-space:nowrap" >Rp {{number_format($value->harga,0,',','.')}}</td>
            <td width="150px">
              <input type="hidden" id="harga_{{$value->id}}" value="{{$value->harga}}">
              <input data-toggle="touchspin" class="center" onchange="calc_belanja()" id="jumlah_{{$value->id}}" name="jumlah[{{$value->id}}]" value="{{$value->jumlah}}" type="text" data-max="{{$value->sisa}}">
            </td>
            <td style="text-align:right;white-space:nowrap" >
              <h6 style="font-weight:600" id="subtotal_{{$value->id}}">Rp {{number_format($value->total,0,',','.')}}</h6>
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
      <input type="hidden" id="action" name="action">
    </form>
  </div>
</div>
<div class="card" style="position:sticky;bottom:0;width:100%;z-index:1000;margin-top:0px">
  <div class="card-body cart-footer" id="cart_footer" >
    <div class="row">
      <div class="col-md-9">
        <div style="font-size:15px;margin-right:10px;align-self: center!important;">Total Harga (<span id="items"></span> Produk)</div>
        <div style="font-size:30px;font-weight:600;color:#409e7c;align-self: center!important;">Rp <span id="total_harga">500.000</span></div>
      </div>
      <div class="col-md-3">
        <button class="btn btn-danger btn-block" onclick="confirm_proses_keranjang('hapus')">Hapus</button>
        <button class="btn btn-primary btn-block" onclick="confirm_proses_keranjang('checkout')">Checkout</button>
      </div>
    </div>
  </div>
</div>
@foreach ($data['keranjang'] as $key => $value)
<form action="{{url('main/belanja/keranjang/hapus')}}" method="post" id="hapus{{$value->id}}">
  {{ csrf_field()}}
  <input type="hidden" name="id" value="{{$value->id}}">
</form>
@endforeach
@endsection
@section('add_js')
<script>
$("#check_all").click(function(){
  $('.pilih_barang').not(this).prop('checked', this.checked);
  calc_belanja();
});
calc_belanja();

function calc_belanja(){
  var pilih = document.getElementsByName('pilih[]');
  var len = pilih.length;
  jumlah_items=total_harga=0;
  for (var i=0; i<len; i++) {
    id=pilih[i].value;
    jumlah=$('#jumlah_'+id).val();
    harga=$('#harga_'+id).val();
    sub_total=jumlah*harga;
    $('#subtotal_'+id).html('Rp '+accounting.formatNumber(sub_total,0,'.',','));
    if(pilih[i].checked){
      jumlah_items=jumlah_items+parseInt(jumlah);
      total_harga=total_harga+sub_total;
    }
  }
  $('#items').html(jumlah_items);
  $('#total_harga').html(accounting.formatNumber(total_harga,0,'.',','));
}

function confirm_proses_keranjang(action){
  if(action=='delete'){
    text="Apakah anda yakin ingin menghapus barang di keranjang ini?";
    color='#d63030';
    label='Yes, delete it!';
  }
  else{
    text="Apakah anda yakin ingin mencheckout barang di keranjang ini?";
    color='#16a085';
    label='Yes';
  }
  Swal.fire({
    title: "Are you sure?",
    text: text,
    type:"question",
    showCancelButton: true,
    confirmButtonColor: color,
    cancelButtonColor: '#cbcbcb',
    confirmButtonText: label
  }).then((result) => {
    if (result.value == true) {
      $('#action').val(action);
      $('#proses_keranjang').submit();
    }
  });
}

</script>
@endsection
